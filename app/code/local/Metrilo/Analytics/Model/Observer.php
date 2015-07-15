<?php 
/**
 * Catch events and track them to metrilo api
 * 
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Model_Observer
{
    /**
     * Identify customer after login
     * 
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $customer = $observer->getEvent()->getCustomer();
        $data = array(
            'id' => $customer->getId(),
            'params' => array(
                'email' => $customer->getEmail(),
                'name' => $customer->getName()
            )
        );
        $helper->addEvent('identify', 'identify', $data);
    }

    /**
     * Track page views
     *   - homepage & CMS pages
     *   - product view pages
     *   - category view pages
     *   - cart view
     *   - checkout
     *   - any other pages (get title from head)
     *   
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function trackPageView(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $action = $observer->getEvent()->getAction()->getFullActionName();
        $pageTracked = false;
        // homepage & CMS pages
        if ($action == 'cms_index_index' || $action == 'cms_page_view')
        {
            $title = Mage::getSingleton('cms/page')->getTitle();
            $helper->addEvent('track', 'pageview', $title);
            $pageTracked = true;
        }
        // category view pages
        if($action == 'catalog_category_view')
        {
            $category = Mage::registry('current_category');
            $data =  array(
                'id'    =>  $category->getId(), 
                'name'  =>  $category->getName()
            );
            $helper->addEvent('track', 'view_category', $data);
            $pageTracked = true;
        }
        // product view pages
        if ($action == 'catalog_product_view')
        {
            $product = Mage::registry('current_product');
            $data =  array(
                'id'    => $product->getId(), 
                'name'  => $product->getName(),
                'price' => number_format($product->getFinalPrice(), 2),
                'url'   => $product->getProductUrl()
            );
            $helper->addEvent('track', 'view_product', $data);
            $pageTracked = true;
        }
        // cart view
        if($action == 'checkout_cart_index')
        {
            $helper->addEvent('track', 'view_cart', array());
            $pageTracked = true;
        }
        // checkout
        if ($action != 'checkout_cart_index' && strpos($action, 'checkout') !== false)
        {
            $helper->addEvent('track', 'checkout_start', array());
            $pageTracked = true;
        }
        // Any other pages
        if(!$pageTracked)
        {
            $title = $observer->getEvent()->getLayout()->getBlock('head')->getTitle();
            $helper->addEvent('track', 'pageview', $title);
        }
    }
}