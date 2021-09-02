<?php

class ModelExtensionModuleAviseme extends Model
{
    public function getRequest($product_id)
    {
        return $this->db->query("SELECT customer_email FROM " . DB_PREFIX . "aviseme_requests WHERE  product_id = '" . $this->db ->escape($product_id) . "'")->rows;
    }

    public function sendNotifications($product_id) : void{
        if ($this->request->server['HTTPS']) {
            $base = HTTPS_SERVER;
        } else {
            $base = HTTP_SERVER;
        }
        $base = explode('/',$base,-2);
        $base = implode('/',$base);

        $this->load->model('catalog/product');
        $pId				= (int)$product_id;
        $pQuery			= 'product_id='.$pId;
        $product		= $this->model_catalog_product->getProduct($pId);
        $url_alias	= $this->db->query("SELECT `keyword` FROM ".DB_PREFIX."seo_url WHERE `query` = '".$pQuery."'");
        $customers	= $this->db->query("SELECT * FROM " . DB_PREFIX . "aviseme_requests WHERE  product_id = '" . $this->db ->escape($product_id) . "'");

        foreach($customers->rows as $cust){
            $this->load->language('catalog/product');

            $text  = '<div> <h3>' . $this->language->get('text_email_body') . htmlspecialchars_decode($product['name']) .'</h3>';


            // seo friendly or stock urls?
            if ($this->config->get('config_seo_url')){
                $text .= '<a  type="button" href= "'. $base . $url_alias->row['keyword']. '">'. $this->language->get('text_go_product') .' </a></div>';
            }	else{
                $text .='<a type="button" href= "'. $base . '/index.php?route=product/product&product_id='.$pId . '">' . $this->language->get('text_go_product'). '</a> </div>';
            }



            // $text .= $base . "\n";

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->protocol      = $this->config->get('config_mail_protocol');
            $mail->parameter     = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($cust['customer_email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject(htmlspecialchars_decode($product['name']) . $this->language->get('text_subject') . $this->config->get('config_name'));
            $mail->sethtml($text);
            $mail->send();
            $this->log->write($cust['customer_email']);

        }

        // remove the registered request, so we don't send them another reminder in the future
        $query = $this->db->query("DELETE FROM " . DB_PREFIX . "aviseme_requests WHERE product_id = '".$pId."' AND customer_email = '". $cust['customer_email'] . "'");
    }
}