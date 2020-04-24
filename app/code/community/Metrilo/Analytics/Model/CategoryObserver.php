<?php
class Metrilo_Analytics_Model_CategoryObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_categorySerializer;
    private $_categoryData;
    
    public function _construct()
    {
        $this->_helper             = Mage::helper('metrilo_analytics');
        $this->_categorySerializer = Mage::helper('metrilo_analytics/categorySerializer');
        $this->_categoryData       = Mage::getModel('metrilo_analytics/categoryData');
    }
    
    public function categoryUpdate($observer)
    {
        try {
            $category        = $observer->getEvent()->getCategory();
            $categoryStoreId = $category->getStoreId();
            
            if ($categoryStoreId == 0) {
                $categoryStoreIds = $this->_helper->getStoreIdsPerProject($category->getStoreIds());
            } else {
                if (!$this->_helper->isEnabled($categoryStoreId)) {
                    return;
                }
                $categoryStoreIds[] = $categoryStoreId;
            }
            foreach ($categoryStoreIds as $storeId) {
                $client             = Mage::helper('metrilo_analytics/apiClient')->getClient($storeId);
                $categoryObject     = $this->_categoryData->getCategoryWithRequestPath($category->getId(), $storeId);
                $serializedCategory = $this->_categorySerializer->serialize($categoryObject);
                $client->category($serializedCategory);
            }
        } catch (Exception $e) {
            $this->_helper->logError('CategoryObserver', $e);
        }
    }
}
