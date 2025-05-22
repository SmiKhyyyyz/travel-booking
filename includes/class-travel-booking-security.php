<?php
/**
 * Security helper functions for Travel Booking plugin
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Security {
    
    /**
     * Validation des paramètres GET/POST renforcée
     */
    public static function validate_booking_token($token) {
        if (empty($token) || !is_string($token) || strlen($token) !== 32) {
            return false;
        }
        return preg_match('/^[a-f0-9]{32}$/', $token);
    }
    
    /**
     * Validation des données de véhicule
     */
    public static function validate_vehicle_data($data) {
        $errors = array();
        
        // Nom du véhicule
        if (empty($data['name']) || strlen($data['name']) > 255) {
            $errors[] = 'Le nom du véhicule est requis et doit faire moins de 255 caractères';
        }
        
        // Capacité
        if (!is_numeric($data['capacity']) || $data['capacity'] < 1 || $data['capacity'] > 50) {
            $errors[] = 'La capacité doit être un nombre entre 1 et 50';
        }
        
        // Prix par km
        if (!is_numeric($data['price_per_km']) || $data['price_per_km'] < 0 || $data['price_per_km'] > 1000) {
            $errors[] = 'Le prix par km doit être un nombre positif inférieur à 1000';
        }
        
        // Prix de base
        if (!is_numeric($data['base_price']) || $data['base_price'] < 0 || $data['base_price'] > 10000) {
            $errors[] = 'Le prix de base doit être un nombre positif inférieur à 10000';
        }
        
        // URL de l'image
        if (!empty($data['image_url']) && !filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'L\'URL de l\'image n\'est pas valide';
        }
        
        return $errors;
    }
    
    /**
     * Validation des permissions d'accès aux fichiers
     */
    public static function secure_file_access($file_path) {
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif');
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            return false;
        }
        
        // Vérifier que le fichier est dans le dossier uploads
        $upload_dir = wp_upload_dir();
        $real_path = realpath($file_path);
        $upload_path = realpath($upload_dir['basedir']);
        
        return strpos($real_path, $upload_path) === 0;
    }
    
    /**
     * Protection CSRF pour les formulaires
     */
    public static function add_csrf_token() {
        if (!session_id()) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(16));
        $_SESSION['travel_booking_csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Vérification CSRF
     */
    public static function verify_csrf_token($token) {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['travel_booking_csrf_token'])) {
            return false;
        }
        
        $valid = hash_equals($_SESSION['travel_booking_csrf_token'], $token);
        unset($_SESSION['travel_booking_csrf_token']);
        
        return $valid;
    }
    
    /**
     * Protection contre les attaques par force brute
     */
    public static function check_login_attempts($ip) {
        $attempts = get_transient('travel_booking_login_attempts_' . $ip);
        
        if ($attempts >= 5) {
            return false; // Trop de tentatives
        }
        
        return true;
    }
    
    /**
     * Enregistrement des tentatives de connexion
     */
    public static function record_login_attempt($ip, $success = false) {
        $attempts = get_transient('travel_booking_login_attempts_' . $ip) ?: 0;
        
        if (!$success) {
            $attempts++;
            set_transient('travel_booking_login_attempts_' . $ip, $attempts, 15 * MINUTE_IN_SECONDS);
        } else {
            delete_transient('travel_booking_login_attempts_' . $ip);
        }
    }
    
    /**
     * Validation sécurisée des paramètres ORDER BY
     */
    public static function sanitize_orderby($orderby, $allowed_columns = array()) {
        if (empty($allowed_columns)) {
            $allowed_columns = array('id', 'name', 'created_at', 'price');
        }
        
        return in_array($orderby, $allowed_columns) ? $orderby : 'id';
    }
    
    /**
     * Validation sécurisée des paramètres ORDER
     */
    public static function sanitize_order($order) {
        return in_array(strtoupper($order), array('ASC', 'DESC')) ? $order : 'DESC';
    }
    
    /**
     * Validation des données de réservation
     */
    public static function validate_booking_data($data) {
        $errors = array();
        
        // Vehicle ID
        if (!isset($data['vehicle_id']) || !is_numeric($data['vehicle_id']) || $data['vehicle_id'] <= 0) {
            $errors[] = 'ID du véhicule invalide';
        }
        
        // Departure
        if (empty($data['departure']) || strlen($data['departure']) > 255) {
            $errors[] = 'Lieu de départ requis et doit faire moins de 255 caractères';
        }
        
        // Destination
        if (empty($data['destination']) || strlen($data['destination']) > 255) {
            $errors[] = 'Destination requise et doit faire moins de 255 caractères';
        }
        
        // Distance
        if (!is_numeric($data['distance']) || $data['distance'] <= 0 || $data['distance'] > 10000) {
            $errors[] = 'Distance invalide (doit être entre 0 et 10000 km)';
        }
        
        // Price
        if (!is_numeric($data['price']) || $data['price'] <= 0 || $data['price'] > 100000) {
            $errors[] = 'Prix invalide (doit être entre 0 et 100000)';
        }
        
        // Passengers
        if (!is_numeric($data['passengers']) || $data['passengers'] < 1 || $data['passengers'] > 50) {
            $errors[] = 'Nombre de passagers invalide (doit être entre 1 et 50)';
        }
        
        // Travel date
        if (empty($data['travel_date']) || !strtotime($data['travel_date'])) {
            $errors[] = 'Date de voyage invalide';
        }
        
        return $errors;
    }
    
    /**
     * Validation des données client
     */
    public static function validate_client_data($data) {
        $errors = array();
        
        // First name
        if (empty($data['first_name']) || strlen($data['first_name']) > 100) {
            $errors[] = 'Prénom requis et doit faire moins de 100 caractères';
        }
        
        // Last name
        if (empty($data['last_name']) || strlen($data['last_name']) > 100) {
            $errors[] = 'Nom requis et doit faire moins de 100 caractères';
        }
        
        // Email
        if (empty($data['email']) || !is_email($data['email'])) {
            $errors[] = 'Email valide requis';
        }
        
        // Phone
        if (empty($data['phone']) || !preg_match('/^[+]?[0-9\s\-\(\)]{10,20}$/', $data['phone'])) {
            $errors[] = 'Numéro de téléphone valide requis';
        }
        
        // Address
        if (empty($data['address']) || strlen($data['address']) > 500) {
            $errors[] = 'Adresse requise et doit faire moins de 500 caractères';
        }
        
        return $errors;
    }
    
    /**
     * Nettoyage des données entrantes
     */
    public static function sanitize_input_data($data, $type = 'text') {
        switch ($type) {
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'textarea':
                return sanitize_textarea_field($data);
            case 'number':
                return is_numeric($data) ? floatval($data) : 0;
            case 'integer':
                return intval($data);
            case 'text':
            default:
                return sanitize_text_field($data);
        }
    }
    
    /**
     * Échappement des sorties HTML
     */
    public static function escape_output($data, $type = 'html') {
        switch ($type) {
            case 'attr':
                return esc_attr($data);
            case 'url':
                return esc_url($data);
            case 'js':
                return esc_js($data);
            case 'html':
            default:
                return esc_html($data);
        }
    }
}

/**
 * Limitation du taux de requêtes pour les API
 */
class Travel_Booking_Rate_Limiter {
    private static $requests = array();
    
    public static function check_rate_limit($ip, $endpoint, $limit = 10, $window = 60) {
        $key = $ip . '_' . $endpoint;
        $now = time();
        
        if (!isset(self::$requests[$key])) {
            self::$requests[$key] = array();
        }
        
        // Nettoyer les anciennes requêtes
        self::$requests[$key] = array_filter(
            self::$requests[$key], 
            function($timestamp) use ($now, $window) {
                return ($now - $timestamp) < $window;
            }
        );
        
        if (count(self::$requests[$key]) >= $limit) {
            return false;
        }
        
        self::$requests[$key][] = $now;
        return true;
    }
    
    /**
     * Obtenir l'IP du client
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}