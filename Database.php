<?php

namespace PetrovEgor;

class Database {
    private $prefix;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->prefix = $wpdb->prefix;
        $this->wpdb = $wpdb;
    }

    public static function updateSchema()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$wpdb->prefix}planes_plugin (
id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(100),
description VARCHAR(1000),
price FLOAT
);";
        dbDelta($sql);
    }

    public function saveNewPlane($post)
    {
        $this->wpdb->insert(
            $this->prefix.'planes_plugin',
            [
                'title' => $post['plane_title'],
                'price' => $post['plane_price'],
                'description' => $post['plane_description'],
            ]
        );
    }

    public function getPlanes()
    {
        $sql = "select * from {$this->prefix}planes_plugin";
        $results = $this->wpdb->get_results($sql, ARRAY_A);
        return $results;
    }
}