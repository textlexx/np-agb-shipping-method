<?php

namespace NpAgbShippingMethod; 

?>

<h1 class="group-h1">
    <?php
    echo TranslatorCenter::run('Main page of groups plugin');
    ?>
</h1>

<h2 class="group-h2">
    <?php
    echo TranslatorCenter::run('Form for creating groups of fields:');
    ?>

	<div class="btn-hide-show-out show-groups-add-form">
        <span>
            <?php echo TranslatorCenter::run('hide/show form'); ?>
            -&gt;
        </span>

        <div class="btn-hide-show">
            <img src="<?php echo URL_DIR_CURRENT_PLG_NP_S_MT; ?>/img/show_hide.png">
        </div>
    </div>
</h2>

<form 
style="margin-top:20px;" 
id="plugin_agb_params" 
class="form-add-group"
action="<?php echo home_url($_SERVER['REQUEST_URI'], wp_get_server_protocol()); ?>" 
method="post"
>
	<label for="field_group_plugin_group_name">
		<?php echo TranslatorCenter::run('Filter type (based on product attribute):'); ?>
	</label>
	<div class="field-reg-log-agb">
		<select 
		name="agb_product_filter_type" 
		id="agb_product_filter_type"
		style="width:1000px;"
		>
			<option value=""><?php echo TranslatorCenter::run('Chose filter type'); ?></option>
			<option value="0"><?php echo TranslatorCenter::run('price'); ?></option>

			<?php 
			$products_filters = Instruments::get_all_filters();
			foreach($products_filters as $key => $filter){ 
			?>
			<option 
			value="<?php echo $filter->attribute_id; ?>"
			slug="<?php echo $filter->attribute_name; ?>"
			>
			<?php echo TranslatorCenter::run($filter->attribute_name); ?>
			</option>
			<?php } ?>
			
		</select>
	</div>

	<label for="field_group_plugin_group_slug">
		<?php echo TranslatorCenter::run('Filter category:'); ?>
	</label>
	<div class="field-reg-log-agb">
		<select 
		name="agb_product_filter_category" 
		id="agb_product_filter_category"
		style="width:1000px;"
		>
			<option value=""><?php echo TranslatorCenter::run('Chose filter category'); ?></option>
			<option value="0"><?php echo TranslatorCenter::run('Without category'); ?></option>

			<?php 
			$products_categories = Instruments::get_all_categories();
			foreach($products_categories as $key => $category){ 
			?>
			<option 
			value="<?php echo $category->term_id; ?>"
			slug="<?php echo $category->slug; ?>"
			>
			<?php echo $category->name; ?>
			</option>
			<?php } ?>

		</select>
	</div>

	<label for="field_group_plugin_group_slug">
		<?php echo TranslatorCenter::run('Filter name (optional):'); ?>
	</label>
	<div class="field-reg-log-agb">
		<input 
		type="text" 
		name="agb_product_filter_name" 
		id="agb_product_filter_name" 
		style="width:1000px;">
	</div>

	<div class="field-from-plugin-agb" style="margin-top:20px;">
		<input type="submit" name="add_filter_btn" id="add_filter_btn" 
		value="<?php echo TranslatorCenter::run('Add'); ?>">
	</div>
</form>

<h2 class="group-h2">
	<?php 
	echo TranslatorCenter::run('List of all products filters:'); 
	?>
</h2>

<div class="all-groups-block">
	<?php
	Instruments::filters_list_output(Instruments::get_all_filters_for_list());
	?>
</div>

<?php
Files_Include_Functions::include_template_php('footer-loader');
?>