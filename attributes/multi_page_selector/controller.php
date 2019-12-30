<?php  

namespace Concrete\Package\MultiPageSelectorAttribute\Attribute\MultiPageSelector;

use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Concrete\Core\Page\Page;

class Controller extends \Concrete\Core\Attribute\Controller  {

	protected $searchIndexFieldDefinition = [
        'type' => 'text',
        'options' => ['default' => null, 'notnull' => false],
    ];
    
	public function getRawValue() {
		$db = \Database::connection();
		$value = $db->fetchColumn("select value from atMultiPageSelector where avID = ?", array($this->getAttributeValueID()));
		return trim($value);
	}

	public function getValue() {
		$value = $this->getRawValue();
		$pages = array();
		$page_ids = array();
		
		if ($value) {
			$page_ids = explode(',', $value);
		}
			
		foreach($page_ids as $pID) {
			$page = \Page::getByID($pID);
			if (!$page->isInTrash()) {
				$pages[] = $page;
			}
		}	
		
		return $pages;
	}
	
	public function getPageLinkArrayValue() {
		$nh = \Core::make('helper/navigation');
		
		$pages = $this->getValue();
		$links = array();
		
		foreach($pages as $p) {
			$links[] = array(
			'cID'=>$p->getCollectionID(),
			'url'=>$nh::getLinkToCollection($p),
			'name'=>$p->getCollectionName(),
			'obj'=>$p);
		}
		
		return $links;			
	}

	public function getSearchIndexValue() {
		$pages = $this->getValue();
		$indexValue = [];
		foreach($pages as $page) {
			// Include both the ID and path
			$indexValue[] = $page->getCollectionID();
			$indexValue[] = $page->getCollectionPath();
		}
		// Follow convention of Topics attribute's indexe value
		$indexValue = '||' . implode('||', $indexValue) . '||';
		return $indexValue;
	}

	public function filterByAttribute(AttributedItemList $list, $value, $comparison = '=')
    {
        if (is_array($value)) {
            $values = $value;
        } else {
            $values = [$value];
        }

        $i = 1;
        $expressions = [];
        $qb = $list->getQueryObject();
        foreach ($values as $value) {
            if ($value instanceof Page) {
                $value = $value->getCollectionID();
            }
            $column = 'ak_' . $this->attributeKey->getAttributeKeyHandle();
            $param = $qb->createNamedParameter('%||' . $value . '||%');
            $expressions[] = $qb->expr()->like($column, $param);
        }

        $expr = $qb->expr();
        $qb->andWhere(call_user_func_array([$expr, 'orX'], $expressions));
    }

	
 	public function form() {
		$this->load();
		$values = array();
		$value = '';

		if (is_object($this->attributeValue)) {
			$values = $this->getAttributeValue()->getValue();
		}

		$ps = \Core::make('helper/form/page_selector');

		$filter = array();

		if ($this->akPtID > 0) {
			$filter['ptID'] = $this->akPtID;
		}

		if ($this->akRestrictSingle) {
			if (is_array($values)) {
				$value = array_shift($values);
			}

            if (version_compare(\Config::get('concrete.version'), '8.2', '>=')) {
                echo $ps->selectFromSiteMap($this->field('value'), $value, $this->akParentID, null, $filter);
            } else {
                echo $ps->selectFromSiteMap($this->field('value'), $value, $this->akParentID, $filter);
            }
		} else {
			echo $ps->selectMultipleFromSitemap($this->field('value'), $values, $this->akParentID, $filter);
		}
	}

	protected function load()
	{
		$ak = $this->getAttributeKey();
		if (!is_object($ak)) {
			return false;
		}

		$db =  \Database::connection();
		$row = $db->query('select akParentID, akPtID, akRestrictSingle from atMultiPageSelectorSettings where akID = ?', array($ak->getAttributeKeyID()));
		$row = $row->fetch();

		$this->akParentID = $row['akParentID'];
		$this->set('akParentID', $this->akParentID)
		;
		$this->akPtID = $row['akPtID'];
		$this->set('akPtID', $this->akPtID);

		$this->akRestrictSingle = $row['akRestrictSingle'];
		$this->set('akRestrictSingle', $this->akRestrictSingle);
	}

	public function type_form() {
		$this->load();
		$pageTypeList = \PageType::getList();
		$this->set('pageTypeList', $pageTypeList);
		$this->set('form', \Core::make('helper/form'));
		$this->set('page_selector', \Core::make('helper/form/page_selector'));
	}

 
	public function saveValue($value) {
		$db = \Database::connection();

		if (is_array($value)) {
			$value = implode(',',$value);
		}

		if (!$value) {
			$value = '';
		}

		$db->Replace('atMultiPageSelector', array('avID' => $this->getAttributeValueID(), 'value' => $value), 'avID', true);
	}

	public function saveKey($data)
	{
		$ak = $this->getAttributeKey();
		$db =\Database::connection();

		$akRestrictSingle = 0;
		if (isset($data['akRestrictSingle']) && $data['akRestrictSingle']) {
			$akRestrictSingle = 1;
		}

		$akPtID = $data['akPtID'];
		$akParentID = $data['akParentID'];

		$db->Replace('atMultiPageSelectorSettings', array(
			'akID' => $ak->getAttributeKeyID(),
			'akParentID' => $akParentID,
			'akPtID' => $akPtID,
			'akRestrictSingle' => $akRestrictSingle,
		), array('akID'), true);
	}


	public function deleteKey() {
		$db = \Database::connection();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->query('delete from atMultiPageSelector where avID = ?', array($id));
		}
	}
	
	public function saveForm($data) {
		$this->saveValue($data['value']);
	}
	
	public function deleteValue() {
		$db = \Database::connection();
		$db->query('delete from atMultiPageSelector where avID = ?', array($this->getAttributeValueID()));
	}
	
}