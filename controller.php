<?php
// Author: Ryan Hewitt - http://www.mesuva.com.au
namespace Concrete\Package\MultiPageSelectorAttribute;

use Package;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use \Concrete\Core\Attribute\Type as AttributeType;

class Controller extends Package {

	protected $pkgHandle = 'multi_page_selector_attribute';
	protected $appVersionRequired = '5.7.5';
	protected $pkgVersion = '0.9.1';
	
	public function getPackageDescription() {
		return t("Attribute that allows the selection of multiple pages");
	}
	
	public function getPackageName() {
		return t("Multi Page Selector Attribute");
	}
	
	public function install() {
		parent::install();
		$pkgh = Package::getByHandle('multi_page_selector_attribute'); 
		$col = AttributeKeyCategory::getByHandle('collection');
		AttributeType::add('multi_page_selector', t('Multi Page Selector'), $pkgh);
		$col->associateAttributeKeyType(AttributeType::getByHandle('multi_page_selector'));
	}
}