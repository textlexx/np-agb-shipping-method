<?php

namespace NpAgbShippingMethod;

use WC_Shipping_Method;

class WC_NP_AGB_Shipping_Method extends WC_Shipping_Method {
        
    public function __construct( $instance_id = 0 ) {
        $this->id                 = 'np_agb_method'; // Уникальный ID метода
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = TranslatorCenter::run('Nova Poshta'); // Название в админке
        $this->method_description = TranslatorCenter::run('Shipping by nova poshta of Ukraine'); // Описание в админке
        $this->supports           = array(
            'shipping-zones',
            'instance-settings',
        );

        $this->init();
    }

    /**
     * Инициализация настроек
     */
    public function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title' );

        // Сохранение настроек в админке
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Настройки метода в панели администратора
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'title' => array(
                'title'       => __( 'Название метода', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'Название, которое видит клиент при оформлении заказа.', 'woocommerce' ),
                'default'     => __( 'Моя доставка', 'woocommerce' ),
                'desc_tip'    => true,
            ),
            'cost' => array(
                'title'       => __( 'Стоимость', 'woocommerce' ),
                'type'        => 'text',
                'placeholder' => '0',
                'description' => __( 'Стоимость доставки (число).', 'woocommerce' ),
                'default'     => '0',
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Расчет стоимости доставки
     */
    public function calculate_shipping( $package = array() ) {
        $cost = $this->get_option( 'cost' );
        
        $rate = array(
            'id'   => $this->id,
            'label' => $this->title,
            'cost'  => $cost,
            'calc_tax' => 'per_order'
        );

        // Регистрация тарифа в WooCommerce
        $this->add_rate( $rate );
    }
}