<?php

namespace NpAgbShippingMethod;

use WC_Shipping_Method;


function create_db_tables(string $tabName):bool{

    global $wpdb;

    $sqlFile = 
    PATH_CURRENT_PLG_NP_S_MT.
    '/sql_tables/'.$wpdb->prefix.$tabName.'.sql';

    clearstatcache();
    if(!file_exists($sqlFile)) return false;

    $sql = file_get_contents($sqlFile);

    if(!$sql) return false;

    $result = $wpdb->query($sql);

    if(!$result) return false;

    return true;
}

function trsltCommonJs():void{

    echo '
    <script>
    window.translate_for_js_fltr_plugin = {
        "trslt_key_1" : '.
        '"'.TranslatorCenter::run('Some translated text 1.').'",'.
        '"trslt_key_2" : '.
        '"'.TranslatorCenter::run('Some translated text 2.').'"
    }
    </script>
    ';
}

function rightWebProtocol():string{

    $protocol = (strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https') ? 'https' : 'http';

    if($_SERVER["SERVER_PORT"] == 443)
        $protocol = 'https';
    elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1')))
        $protocol = 'https';
    elseif (
        !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || 
        !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
    )
        $protocol = 'https';
    elseif (!empty($_SERVER["REQUEST_SCHEME"]) && $_SERVER["REQUEST_SCHEME"] == 'https')
        $protocol = 'https';
        
    return $protocol.'://';
}

function add_my_custom_shipping_method( $methods ) {

    $methods['my_custom_shipping'] = 'WC_My_Custom_Shipping_Method';
    return $methods;
}
function init_my_custom_shipping_method() {

    if ( class_exists( 'WC_Shipping_Method' ) ) {

        class WC_My_Custom_Shipping_Method extends WC_Shipping_Method {

            public function __construct( $instance_id = 0 ) {

                $this->id                 = 'my_custom_shipping'; // Уникальный ID
                $this->method_title       = 'Моя доставка'; // Название в админке
                $this->method_description = 'Описание вашего способа доставки';
                $this->instance_id        = $instance_id;
                
                $this->init();
                $this->enabled = $this->get_option( 'enabled' );
                $this->title   = $this->get_option( 'title' );
            }

            public function init() {

                // Инициализация настроек
                $this->init_form_fields();
                $this->init_settings();

                // Сохранение настроек
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            public function init_form_fields() {

                $this->form_fields = array(
                    'enabled' => array(
                        'title'   => 'Включить',
                        'type'    => 'checkbox',
                        'label'   => 'Включить этот способ доставки',
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title'       => 'Название',
                        'type'        => 'text',
                        'description' => 'Название для покупателя',
                        'default'     => 'Моя доставка'
                    ),
                );
            }

            public function calculate_shipping( $package = array() ) {

                // Логика расчета стоимости
                $cost = 150; // Пример фиксированной цены
                
                $this->add_rate( array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => $cost,
                    'calc_tax' => 'per_item'
                ) );
            }
        }
    }
}


?>