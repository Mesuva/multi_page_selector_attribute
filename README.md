# Multi Page Selector Attribute

A multiple page selector attribute for concrete5 version 5.7.

- Allow multiple pages to be selected from the sitemap
- Sitemap can be filtered by section and/or by page type
- Can also be restricted to allow the selection of one page only

Once installed, you can fetch the attribute in a page template one of two ways:

```php
$products = $c->getAttribute('related_products', 'pageArray');
// $products now contains an array of collection (page) objects

// or 
$products = $c->getAttribute('related_products', 'pageLinkArray');
// $products now contains an array of arrays, each containing 'cID' 'url', 'name', and 'obj' (the original page object) meaning you can do:

if (!empty($products)) { 
    echo '<ul>';
    foreach($products as $prod) {
        echo '<li><a href="' . $prod['url'] . '">'. $prod['name']. '</a></li>';
    }
    echo '</ul>';
}
```

