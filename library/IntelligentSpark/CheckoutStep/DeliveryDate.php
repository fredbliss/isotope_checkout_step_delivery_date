<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2016 Intelligent Spark
 *
 * @package    Isotope Custom Step "Delivery Date"
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace IntelligentSpark\CheckoutStep;

use Isotope\Module\Checkout;
use Isotope\CheckoutStep\CheckoutStep;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Template;
use Hofff\Contao\Calendarfield\FormCalendarField;



class DeliveryDate extends CheckoutStep implements IsotopeCheckoutStep {

    protected $strTemplate = 'iso_checkout_step_delivery_date';

    //protected $strFormId = 'iso_checkout_delivery_date';

    protected $strTable = 'tl_iso_product_collection';
    
    protected $strField = 'delivery_date';

    /**
     * Returns true if order conditions are defined
     * @return  bool
     */
    public function isAvailable()
    {
        $objOrder = Isotope::getCart()->getDraftOrder();
        
        $arrItems = $objOrder->getItems();

        foreach($arrItems as $objItem) {
            $objProduct = $objItem->getProduct();

            if($objProduct->getType()->id==4 || $objProduct->getType()->id==7 || $objProduct->delivery_date=='1') {
                return true;
            }
        }

        return false;
    }

    public function isSkippable() {
        return false;
    }

    /**
     * Return true if the step has an error and forwarding should be cancelled
     * @return  bool
     */
    public function hasError() {
        return false;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return  string
     */
    public function getStepClass() {
        $strClass = get_parent_class($this);
        $strClass = substr($strClass, strrpos($strClass, '\\') + 1);

        return parent::getStepClass() . ' ' . standardize($strClass);
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $objTemplate = new Template($this->strTemplate);
        \System::loadLanguageFile($this->strTable);

        $arrAttributes = ['name'=>$this->strField,'id'=>$this->strField,'label'=>$GLOBALS['TL_LANG'][$this->strTable][$this->strField][0],'dateDirection'=>'gtToday','inputType'=>'calendarfield','dateImage'=>true,'dateIncludeCSS'=>true, 'dateIncludeCSSTheme'=>'ui-lightness','eval'=>['mandatory'=>true,'rgxp'=>'date', 'datepicker'=>true]];

        $varValue = null;

        //$objWidget = new FormCalendarField(FormCalendarField::getAttributesFromDca($arrAttributes, $this->strField, $varValue, $this->strField, $this->strTable));
        $objWidget = new FormCalendarField($arrAttributes);
        $objWidget->storeValues = true;
        $objWidget->mandatory = true;

        if (\Input::post('FORM_SUBMIT') == 'iso_mod_checkout_review')
        {
            $objWidget->validate();
            $varValue = $objWidget->value;

            $rgxp = $arrAttributes['eval']['rgxp'];

            // Convert date formats into timestamps (check the eval setting first -> #3063)
            /*if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim')))
            {
                try
                {
                    $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                    $varValue = $objDate->tstamp;
                }
                catch (\OutOfBoundsException $e)
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                }
            }*/

            // Do not submit the field if there are errors
            if ($objWidget->hasErrors())
            {
                $doNotSubmit = true;
            }
            elseif ($objWidget->submitInput())
            {
                // Store the form data
                $_SESSION['FORM_DATA'][$this->strField] = $varValue;

                // Set the correct empty value (see #6284, #6373)
                if ($varValue === '')
                {
                    $varValue = $objWidget->getEmptyValue();
                }

                Isotope::getCart()->{$this->strField} = $varValue;
            }
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['delivery_date'];
        $objTemplate->datePicker = $objWidget->parse();

        return $objTemplate->parse();
    }


    /**
     * Get review information about this step
     * @return  array
     */
    public function review() {

        return array();
    }
    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {


        return [$this->strField=>$objCollection->{$this->strField}];
    }

}