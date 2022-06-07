<?php // exit if uninstall constant is not defined
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

//remove power_bi specifically created options
$options = ['power_bi_settings', '_powerbi_embed_flushed', 'power_bi_credentials','power_bi_management_azure_credentials','page_on_front'];
foreach($options as $option){
    if(get_option($option)) delete_option($option);
}

//remove token transient
delete_transient('t_token');