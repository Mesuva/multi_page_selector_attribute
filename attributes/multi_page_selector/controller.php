<?php

namespace Concrete\Package\MultiPageSelectorAttribute\Attribute\MultiPageSelector;

use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Database;

class Controller extends \Concrete\Core\Attribute\Controller  {

    public function getIconFormatter()
    {
        return new \Concrete\Core\Attribute\FontAwesomeIconFormatter('link');
    }

	public function getRawValue() {
		$db = Database::connection();
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
			$page = Page::getByID($pID);
			if (!$page->isInTrash()) {
				$pages[] = $page;
			}
		}

		return $pages;
	}

	public function getPageLinkArrayValue() {
		$nh = app()->make('helper/navigation');

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


 	public function form() {
		$this->load();
		$values = array();
		$value = '';

		if (is_object($this->attributeValue)) {
			$values = $this->getAttributeValue()->getValue();
		}

		$ps = app()->make('helper/form/page_selector');

		$filter = array();

		if ($this->akPtID > 0) {
			$filter['ptID'] = $this->akPtID;
		}

		if ($this->akRestrictSingle) {
			if (is_array($values)) {
				$value = array_shift($values);
			}

            if (version_compare(Config::get('concrete.version'), '8.2', '>=')) {
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

		$db =  Database::connection();
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
		$pageTypeList = Type::getList();
		$this->set('pageTypeList', $pageTypeList);
		$this->set('form', app()->make('helper/form'));
		$this->set('page_selector', app()->make('helper/form/page_selector'));
	}


	public function saveValue($value) {
		$db = Database::connection();

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
		$db =Database::connection();

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
		$db = Database::connection();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->query('delete from atMultiPageSelector where avID = ?', array($id));
		}
	}

    public function saveForm($data) {
        $attData = $this->request->request->get('akID');

        if (isset($attData[$this->getAttributeKey()->getAttributeKeyID()])) {
            $this->saveValue($attData[$this->getAttributeKey()->getAttributeKeyID()]['value']);
        } else {
            $this->saveValue([]);
        }
    }

	public function deleteValue() {
		$db = Database::connection();
		$db->query('delete from atMultiPageSelector where avID = ?', array($this->getAttributeValueID()));
	}

}
