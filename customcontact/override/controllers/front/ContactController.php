<?php

class ContactController extends ContactControllerCore
{
	public function setMedia()
	{   
		parent::setMedia();
		$this->addCSS($this->getModulePath() . '/views/css/custom-contact.css');
		$this->addJS($this->getModulePath() . '/views/js/custom-contact.js');
	}

	/**
    * Assign template vars related to page content
    * @see FrontController::initContent()
    */
	public function initContent()
    {
        parent::initContent();

        $this->assignOrderList();

        $email = Tools::safeOutput(Tools::getValue('from',
        ((isset($this->context->cookie) && isset($this->context->cookie->email) && Validate::isEmail($this->context->cookie->email)) ? $this->context->cookie->email : '')));
        $this->context->smarty->assign(array(
            'errors' => $this->errors,
            'email' => $email,
            'fileupload' => Configuration::get('PS_CUSTOMER_SERVICE_FILE_UPLOAD'),
            'max_upload_size' => (int)Tools::getMaxUploadSize()
        ));

        if (($id_customer_thread = (int)Tools::getValue('id_customer_thread')) && $token = Tools::getValue('token')) {
            $customer_thread = Db::getInstance()->getRow('
				SELECT cm.*
				FROM '._DB_PREFIX_.'customer_thread cm
				WHERE cm.id_customer_thread = '.(int)$id_customer_thread.'
				AND cm.id_shop = '.(int)$this->context->shop->id.'
				AND token = \''.pSQL($token).'\'
			');

            $order = new Order((int)$customer_thread['id_order']);
            if (Validate::isLoadedObject($order)) {
                $customer_thread['reference'] = $order->getUniqReference();
            }
            $this->context->smarty->assign('customerThread', $customer_thread);
        }

        $this->context->smarty->assign(array(
            'contacts' => Contact::getContacts($this->context->language->id),
            'message' => html_entity_decode(Tools::getValue('message'))
        ));

        $map_settings = Tools::jsonDecode(Configuration::get('PS_CUSTOM_CONTACT_MAP'), true);
        foreach ($map_settings as $setting => $value) {
        	if ($setting == "MAP_MARKER" && $value)
                $this->context->smarty->assign(strtolower($setting), $this->getMarkerURL($value));
            else
        		$this->context->smarty->assign(strtolower($setting), $value);
        }

        $tpl_override = _PS_THEME_DIR_ . 'modules' . DIRECTORY_SEPARATOR . 'customcontact' . DIRECTORY_SEPARATOR . 'views' .DIRECTORY_SEPARATOR . 'templates' .DIRECTORY_SEPARATOR. 'front'. DIRECTORY_SEPARATOR. 'contact-form.tpl';
        if (file_exists($tpl_override))
            $this->setTemplate($tpl_override);
        else
            $this->setTemplate($this->getModulePath() . DIRECTORY_SEPARATOR . 'views' .DIRECTORY_SEPARATOR . 'templates' .DIRECTORY_SEPARATOR. 'front'. DIRECTORY_SEPARATOR. 'contact-form.tpl');               
    }

	/**
    * Get module name
    * @return string
    */
    private function getModuleName()
    {
        return 'customcontact';
    }

	/**
    * Get path to module
    * @return string
    */
    private function getModulePath()
    {
        return _PS_MODULE_DIR_ . $this->getModuleName();
    }

	/**
    * Get url path to map marker
    * @return string
    */
	private function getMarkerURL($markerImgFilename)
	{
		return $this->context->link->protocol_content.Tools::getMediaServer($markerImgFilename)._MODULE_DIR_.'customcontact/views/img/' . $markerImgFilename;
	}

}