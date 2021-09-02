<?php

class ModelExtensionModuleAviseme extends Model
{
    public function install()
    {
        $this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aviseme_requests` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`customer_email` CHAR(50) NOT NULL,
					`product_id` INT(11) NOT NULL,
				    PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

    }

    public function createRequest($data){
        if ($this->db->query("SELECT id FROM " . DB_PREFIX . "aviseme_requests WHERE customer_email = '" . $this->db->escape($data['customer_email']) . "'AND product_id = '" . $this->db ->escape($data['product_id']) . "'")->rows == null) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "aviseme_requests SET customer_email = '" . $this->db->escape($data['customer_email']) . "',product_id = '" . $this->db->escape($data['product_id']) . "'");
        }
    }


}