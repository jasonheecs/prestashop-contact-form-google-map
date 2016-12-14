<?php
/**
 * @author Jason Hee <https://github.com/jasonheecs>
 * @license http://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_'))
	exit;

class CustomContact extends Module 
{
	protected static $map_fields = array(
		'MAP_CENTER_LAT',
		'MAP_CENTER_LONG',
		'MAP_MARKER',
		'MAP_MARKER_LAT',
		'MAP_MARKER_LONG',
        'MAP_MARKER_TITLE',
		'MAP_INFOBOX',
        'MAP_API'
	);

	public function __construct() 
	{
		$this->name = 'customcontact';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'Jason Hee';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Custom Contact Form');
		$this->description = $this->l('A Custom Prestashop contact form with a Google Map background');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}

	public function install()
	{
		$default_lat = Configuration::get('PS_STORES_CENTER_LAT') ?  Configuration::get('PS_STORES_CENTER_LAT') : 38.535154;
        $default_long = Configuration::get('PS_STORES_CENTER_LONG') ? Configuration::get('PS_STORES_CENTER_LONG') : -77.2840;
        $default_title = Configuration::get('PS_SHOP_NAME') ? Configuration::get('PS_SHOP_NAME') : '';

        $shop_addr1 = Configuration::get('PS_SHOP_ADDR1') ? Configuration::get('PS_SHOP_ADDR1') : '';
        $shop_addr2 = Configuration::get('PS_SHOP_ADDR2') ? Configuration::get('PS_SHOP_ADDR2') : '';
        $default_infobox = "<p>$shop_addr1</p><p>$shop_addr2</p>";

        $map_settings = array(
            'MAP_CENTER_LAT'   => $default_lat,
            'MAP_CENTER_LONG'  => $default_long,
            'MAP_MARKER'       => '',
            'MAP_MARKER_LAT'   => $default_lat,
            'MAP_MARKER_LONG'  => $default_long,
            'MAP_MARKER_TITLE' => $default_title,
            'MAP_INFOBOX'      => "<h3>$default_title</h3>$default_infobox",
            'MAP_API'          => ''
        );
        Configuration::updateValue('PS_CUSTOM_CONTACT_MAP', json_encode($map_settings, JSON_HEX_QUOT | JSON_HEX_TAG));    

		$this->_clearCache('custom-form.tpl');
		$this->_clearCache('map.tpl');
		return parent::install();
	}

	public function uninstall()
	{
		Configuration::deleteByName('PS_CUSTOM_CONTACT_MAP');
		return parent::uninstall();
	}

	public function getContent()
    {
    	return $this->postProcess() . $this->displayForm();
    }

    public function postProcess()
    {
        $output = null;        

        if (Tools::isSubmit('submit'. $this->name))
        {
            //Map Marker
            $update_images_values = false;
            $map_settings = Tools::jsonDecode(Configuration::get('PS_CUSTOM_CONTACT_MAP'), true);                       

            if (isset($_FILES['MAP_MARKER'])
                && isset($_FILES['MAP_MARKER']['tmp_name'])
                && !empty($_FILES['MAP_MARKER']['tmp_name']))
            {
                if ($error = ImageManager::validateUpload($_FILES['MAP_MARKER'], 4000000))
                    return $error;
                else
                {
                    $ext = substr($_FILES['MAP_MARKER']['name'], strrpos($_FILES['MAP_MARKER']['name'], '.') + 1);
                    $previous_marker = $map_settings["MAP_MARKER"];
                    $new_marker = md5($_FILES['MAP_MARKER']['name']) . '.' . $ext;

                    if (!move_uploaded_file($_FILES['MAP_MARKER']['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$new_marker))
                        $output .= $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                    else 
                    {                        
                        if ($previous_marker != $new_marker)
                        {
                            @unlink(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$previous_marker);
                        }

                        $svg_alt_icon = md5($_FILES['MAP_MARKER']['name']) . '.svg';
                        if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$svg_alt_icon))
                            $new_marker = $svg_alt_icon;
                    }
                }

                $update_images_values = true;
            }

            //Update fields
            foreach (CustomContact::$map_fields as $field) 
            {
                if ($field == "MAP_MARKER")
                {
                    if ($update_images_values && $new_marker)
                        $map_settings[$field] = $new_marker;
                } 
                else 
                    $map_settings[$field] = strval(Tools::getValue($field));

            }
            if (!$map_settings || empty($map_settings)) 
                $output .= $this->displayError($this->l('Invalid Configuration Value'));
            else
            {
                Configuration::updateValue('PS_CUSTOM_CONTACT_MAP', json_encode($map_settings, JSON_HEX_QUOT | JSON_HEX_TAG));
                $output .= $this->displayConfirmation($this->l('Settings Updated'));
            }
        
        }
        return $output;
    }

    public function displayForm()
    {
    	$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    	$map_settings = Tools::jsonDecode(Configuration::get('PS_CUSTOM_CONTACT_MAP'), true);

    	$fields_form[0]['form'] = array(
    		'legend' => array(
    			'title' => $this->l('Map Settings'),
    			'icon' => 'icon-cogs'
    		),
    		'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Google Maps API key'),
                    'name' => 'MAP_API',
                    'required' => true,
                    'desc' => 'The API Key from Google Maps. 
                               Refer to 
                               <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                               https://developers.google.com/maps/documentation/javascript/get-api-key</a> 
                               on how to get an api key'
                ),
    			array(
    				'type' => 'text',
    				'label' => $this->l('Map Center Latitude'),
    				'name' => 'MAP_CENTER_LAT',
    				'required' => true,
    				'desc' => 'Latitude of where the map will be centered on'
    			),
    			array(
    				'type' => 'text',
    				'label' => $this->l('Map Center Longitude'),
    				'name' => 'MAP_CENTER_LONG',
    				'required' => true,
    				'desc' => 'Longitude of where the map will be centered on'
    			),
    			array(
    				'type' => 'file_lang',
    				'label' => $this->l('Map Marker Image Icon'),
    				'name' => 'MAP_MARKER',
    				'desc' => 'Image of the map marker',
                    'hint' => $this->l('Upload a marker image from your computer.')
    			),
    			array(
    				'type' => 'text',
    				'label' => $this->l('Map Marker Latitude'),
    				'name' => 'MAP_MARKER_LAT',
    				'desc' => 'Latitude of the map marker'
    			),
    			array(
    				'type' => 'text',
    				'label' => $this->l('Map Marker Longitude'),
    				'name' => 'MAP_MARKER_LONG',
    				'desc' => 'Longitude of the map marker'
    			),
                array(
                    'type' => 'text',
                    'label' => $this->l('Map Marker title'),
                    'name' => 'MAP_MARKER_TITLE',
                    'desc' => 'Title of the marker',
                    'hint' => $this->l('Appears when you hover over the marker.')
                ),
    			array(
    				'type' => 'textarea',
    				'label' => $this->l('Map Marker Infobox description'),
    				'name' => 'MAP_INFOBOX',
    				'desc' => 'The information that will appear when the map marker is clicked on',
    				'rows' => '12',
                    'class' => 'rte',
                    'autoload_rte' => true,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}'
    			)
    		),
    		'submit' => array(
    			'title' => $this->l('Save')
    		)
    	);

    	$helper = new HelperForm();

    	$helper->module = $this;
    	$helper->name_controller = $this->name;
    	$helper->token = Tools::getAdminTokenLite('AdminModules');
    	$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

    	$helper->default_form_language = $default_lang;
    	$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
    	$helper->tpl_vars = array(
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
            'uri' => $this->getPathUri()
		);

    	$helper->title = $this->displayName;
    	$helper->show_toolbar= false;
    	$helper->submit_action = 'submit' . $this->name;

    	$helper->table = $this->table;
    	$helper->identifier = $this->identifier;

    	foreach (CustomContact::$map_fields as $field) {
    		$helper->fields_value[$field] = $map_settings[$field];
    	}

    	return $helper->generateForm($fields_form);
    }
}