<?php
/**
 * Travel Booking Email System - VERSION MISE À JOUR
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

class Travel_Booking_Emails {
    
    /**
     * Initialize email hooks
     */
    public static function init() {
        // Hook pour l'email de confirmation de commande
        add_action('woocommerce_order_status_pending', array(__CLASS__, 'send_booking_confirmation_email'));
        add_action('woocommerce_order_status_processing', array(__CLASS__, 'send_booking_confirmation_email'));
        
        // Hook pour l'email de confirmation de réservation
        add_action('woocommerce_order_status_completed', array(__CLASS__, 'send_booking_confirmed_email'));
        
        // Hook pour l'email d'annulation
        add_action('woocommerce_order_status_cancelled', array(__CLASS__, 'send_booking_cancelled_email'));

        // Hook pour la notification administrateur lors d'une nouvelle commande
        add_action('woocommerce_order_status_pending', array(__CLASS__, 'send_admin_notification_email'));
        add_action('woocommerce_new_order', array(__CLASS__, 'send_admin_notification_email'));
    }
    
    /**
     * Envoyer l'email de confirmation de commande (après paiement)
     */
    public static function send_booking_confirmation_email($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $booking_token = $order->get_meta('_travel_booking_token');
        if (!$booking_token) return;
        
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        if (!$booking) return;
        
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        if (!$vehicle) return;
        
        // Données pour l'email
        $email_data = array(
            'booking' => $booking,
            'vehicle' => $vehicle,
            'order' => $order,
            'order_id' => $order_id
        );
        
        // Envoyer l'email
        self::send_email(
            $booking->client_email,
            'Confirmation de votre réservation de transport - Commande #' . $order_id,
            'booking_confirmation',
            $email_data
        );
    }

    /**
     * Envoyer une notification à l'administrateur pour une nouvelle réservation
     */
    public static function send_admin_notification_email($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $booking_token = $order->get_meta('_travel_booking_token');
        if (!$booking_token) return;
        
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        if (!$booking) return;
        
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        if (!$vehicle) return;
        
        // Données pour l'email
        $email_data = array(
            'booking' => $booking,
            'vehicle' => $vehicle,
            'order' => $order,
            'order_id' => $order_id
        );
        
        // Adresse email de l'administrateur
        $admin_email = get_option('admin_email');
        
        // Envoyer l'email
        self::send_email(
            $admin_email,
            'Nouvelle réservation de transport - Commande #' . $order_id,
            'admin_notification',
            $email_data
        );
    }
    
    /**
     * Envoyer l'email de confirmation de réservation (course confirmée)
     */
    public static function send_booking_confirmed_email($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $booking_token = $order->get_meta('_travel_booking_token');
        if (!$booking_token) return;
        
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        if (!$booking) return;
        
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        if (!$vehicle) return;
        
        // Mettre à jour le statut de la réservation
        Travel_Booking_Booking::update_status($booking->id, 'confirmed');
        
        // Données pour l'email
        $email_data = array(
            'booking' => $booking,
            'vehicle' => $vehicle,
            'order' => $order,
            'order_id' => $order_id
        );
        
        // Envoyer l'email
        self::send_email(
            $booking->client_email,
            'Votre transport est confirmé - Réservation #' . $booking->id,
            'booking_confirmed',
            $email_data
        );
    }
    
    /**
     * Envoyer l'email d'annulation
     */
    public static function send_booking_cancelled_email($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $booking_token = $order->get_meta('_travel_booking_token');
        if (!$booking_token) return;
        
        $booking = Travel_Booking_Booking::get_by_token($booking_token);
        if (!$booking) return;
        
        $vehicle = Travel_Booking_Vehicle::get($booking->vehicle_id);
        if (!$vehicle) return;
        
        // Mettre à jour le statut de la réservation
        Travel_Booking_Booking::update_status($booking->id, 'cancelled');
        
        // Données pour l'email
        $email_data = array(
            'booking' => $booking,
            'vehicle' => $vehicle,
            'order' => $order,
            'order_id' => $order_id
        );
        
        // Envoyer l'email
        self::send_email(
            $booking->client_email,
            'Annulation de votre réservation de transport - #' . $booking->id,
            'booking_cancelled',
            $email_data
        );
    }
    
    /**
     * Fonction générique pour envoyer un email
     */
    private static function send_email($to, $subject, $template, $data) {
        // Récupérer les paramètres email depuis les options
        $from_name = get_option('travel_booking_email_from_name', get_bloginfo('name'));
        $from_email = get_option('travel_booking_email_from_email', get_option('admin_email'));
        
        // Headers pour l'email HTML
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );
        
        // Générer le contenu HTML de l'email
        $message = self::get_email_template($template, $data);
        
        // Envoyer l'email
        wp_mail($to, $subject, $message, $headers);
        
        // Log pour debug
        error_log("Email envoyé à {$to} - Template: {$template}");
    }
    
    /**
     * Générer le template HTML de l'email
     */
    private static function get_email_template($template, $data) {
        extract($data);
        
        $company_name = get_option('travel_booking_email_from_name', get_bloginfo('name'));
        $company_logo = get_option('travel_booking_email_logo', '');
        $footer_text = get_option('travel_booking_email_footer_text', '');
        
        switch ($template) {
            case 'booking_confirmation':
                return self::booking_confirmation_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text);
                
            case 'booking_confirmed':
                return self::booking_confirmed_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text);
                
            case 'booking_cancelled':
                return self::booking_cancelled_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text);

            case 'admin_notification':
                return self::admin_notification_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text);
                
            default:
                return '';
        }
    }
    
    /**
     * Template email de confirmation de commande
     */
    private static function booking_confirmation_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text) {
        $travel_date = date_i18n('d/m/Y à H:i', strtotime($booking->travel_date));
        $price = number_format($booking->price, 2) . ' CHF';
        
        $logo_html = '';
        if (!empty($company_logo)) {
            $logo_html = '<img src="' . esc_url($company_logo) . '" alt="' . esc_attr($company_name) . '" style="max-height: 60px; margin-bottom: 15px;">';
        }
        
        $footer_html = '';
        if (!empty($footer_text)) {
            $footer_html = '<p>' . esc_html($footer_text) . '</p>';
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Confirmation de réservation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #666; }
                .detail-value { color: #333; }
                .price-highlight { background: #d3b27f; color: white; padding: 15px; text-align: center; border-radius: 8px; font-size: 1.2em; font-weight: bold; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    ' . $logo_html . '
                    <h1>Confirmation de votre réservation</h1>
                    <p>Merci pour votre commande !</p>
                </div>
                
                <div class="content">
                    <h2>Bonjour ' . esc_html($booking->client_first_name) . ',</h2>
                    
                    <p>Votre commande #' . $order_id . ' a été reçue et est en cours de traitement. Voici les détails de votre réservation de transport :</p>
                    
                    <div class="booking-details">
                        <h3>Détails du voyage</h3>
                        <div class="detail-row">
                            <span class="detail-label">Véhicule :</span>
                            <span class="detail-value">' . esc_html($vehicle->name) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Départ :</span>
                            <span class="detail-value">' . esc_html($booking->departure) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Destination :</span>
                            <span class="detail-value">' . esc_html($booking->destination) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date et heure :</span>
                            <span class="detail-value">' . $travel_date . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Passagers :</span>
                            <span class="detail-value">' . $booking->number_of_passengers . ' personne(s)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Distance :</span>
                            <span class="detail-value">' . number_format($booking->distance, 2) . ' km</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Aller-retour :</span>
                            <span class="detail-value">' . ($booking->round_trip ? 'Oui' : 'Non') . '</span>
                        </div>
                        ' . (!empty($booking->flight_number) ? '<div class="detail-row"><span class="detail-label">N° de vol :</span><span class="detail-value">' . esc_html($booking->flight_number) . '</span></div>' : '') . '
                    </div>
                    
                    <div class="price-highlight">
                        Prix total : ' . $price . '
                    </div>
                    
                    <p><strong>Prochaines étapes :</strong></p>
                    <ul>
                        <li>Votre paiement est en cours de traitement</li>
                        <li>Vous recevrez une confirmation définitive une fois le paiement validé</li>
                        <li>Notre équipe vous contactera 24h avant votre voyage pour confirmer les détails</li>
                    </ul>
                    
                    <p>Si vous avez des questions, n\'hésitez pas à nous contacter.</p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . $company_name . ' - Tous droits réservés</p>
                    <p>Email: ' . get_option('admin_email') . '</p>
                    ' . $footer_html . '
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Template email de confirmation de réservation
     */
    private static function booking_confirmed_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text) {
        $travel_date = date_i18n('d/m/Y à H:i', strtotime($booking->travel_date));
        $price = number_format($booking->price, 2) . ' CHF';
        
        $logo_html = '';
        if (!empty($company_logo)) {
            $logo_html = '<img src="' . esc_url($company_logo) . '" alt="' . esc_attr($company_name) . '" style="max-height: 60px; margin-bottom: 15px;">';
        }
        
        $footer_html = '';
        if (!empty($footer_text)) {
            $footer_html = '<p>' . esc_html($footer_text) . '</p>';
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Votre transport est confirmé</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #666; }
                .detail-value { color: #333; }
                .important-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    ' . $logo_html . '
                    <h1>🚗 Votre transport est confirmé !</h1>
                    <p>Tout est prêt pour votre voyage</p>
                </div>
                
                <div class="content">
                    <h2>Bonjour ' . esc_html($booking->client_first_name) . ',</h2>
                    
                    <p><strong>Excellente nouvelle !</strong> Votre réservation de transport est maintenant confirmée et votre chauffeur vous attend.</p>
                    
                    <div class="booking-details">
                        <h3>Détails de votre voyage</h3>
                        <div class="detail-row">
                            <span class="detail-label">Véhicule :</span>
                            <span class="detail-value">' . esc_html($vehicle->name) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Départ :</span>
                            <span class="detail-value">' . esc_html($booking->departure) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Destination :</span>
                            <span class="detail-value">' . esc_html($booking->destination) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date et heure :</span>
                            <span class="detail-value">' . $travel_date . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Passagers :</span>
                            <span class="detail-value">' . $booking->number_of_passengers . ' personne(s)</span>
                        </div>
                        ' . (!empty($booking->flight_number) ? '<div class="detail-row"><span class="detail-label">N° de vol :</span><span class="detail-value">' . esc_html($booking->flight_number) . '</span></div>' : '') . '
                    </div>
                    
                    <div class="important-info">
                        <h4>📋 Informations importantes :</h4>
                        <ul>
                            <li><strong>Soyez prêt 15 minutes avant l\'heure prévue</strong></li>
                            <li>Notre chauffeur vous contactera 30 minutes avant l\'arrivée</li>
                            <li>Ayez votre téléphone à portée de main</li>
                            <li>En cas d\'imprévu, contactez-nous immédiatement</li>
                        </ul>
                    </div>
                    
                    <p><strong>Numéro de réservation :</strong> #' . $booking->id . '</p>
                    <p><strong>Numéro de commande :</strong> #' . $order_id . '</p>
                    
                    <p>Nous vous souhaitons un excellent voyage !</p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . $company_name . ' - Tous droits réservés</p>
                    <p>Email: ' . get_option('admin_email') . ' | Urgences: [VOTRE NUMÉRO D\'URGENCE]</p>
                    ' . $footer_html . '
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Template email d'annulation
     */
    private static function booking_cancelled_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text) {
        $logo_html = '';
        if (!empty($company_logo)) {
            $logo_html = '<img src="' . esc_url($company_logo) . '" alt="' . esc_attr($company_name) . '" style="max-height: 60px; margin-bottom: 15px;">';
        }
        
        $footer_html = '';
        if (!empty($footer_text)) {
            $footer_html = '<p>' . esc_html($footer_text) . '</p>';
        }
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Annulation de réservation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #e74c3c; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    ' . $logo_html . '
                    <h1>Annulation de votre réservation</h1>
                </div>
                
                <div class="content">
                    <h2>Bonjour ' . esc_html($booking->client_first_name) . ',</h2>
                    
                    <p>Nous vous informons que votre réservation #' . $booking->id . ' (commande #' . $order_id . ') a été annulée.</p>
                    
                    <p>Si cette annulation n\'était pas attendue, veuillez nous contacter immédiatement.</p>
                    
                    <p>Le remboursement sera traité selon nos conditions générales de vente.</p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . $company_name . '</p>
                    <p>Email: ' . get_option('admin_email') . '</p>
                    ' . $footer_html . '
                </div>
            </div>
        </body>
        </html>';
    }

    /**
 * Template email de notification administrateur
 */
private static function admin_notification_template($booking, $vehicle, $order, $order_id, $company_name, $company_logo, $footer_text) {
    $travel_date = date_i18n('d/m/Y à H:i', strtotime($booking->travel_date));
    $price = number_format($booking->price, 2) . ' CHF';
    
    $logo_html = '';
    if (!empty($company_logo)) {
        $logo_html = '<img src="' . esc_url($company_logo) . '" alt="' . esc_attr($company_name) . '" style="max-height: 60px; margin-bottom: 15px;">';
    }
    
    $footer_html = '';
    if (!empty($footer_text)) {
        $footer_html = '<p>' . esc_html($footer_text) . '</p>';
    }
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Nouvelle réservation de transport</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; }
            .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .detail-label { font-weight: bold; color: #666; }
            .detail-value { color: #333; }
            .customer-info { background: #e8f4fc; padding: 15px; border-radius: 8px; margin-top: 20px; }
            .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                ' . $logo_html . '
                <h1>Nouvelle réservation de transport</h1>
                <p>Une nouvelle réservation a été reçue !</p>
            </div>
            
            <div class="content">
                <h2>Détails de la réservation #' . $booking->id . '</h2>
                
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="detail-label">Véhicule :</span>
                        <span class="detail-value">' . esc_html($vehicle->name) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Départ :</span>
                        <span class="detail-value">' . esc_html($booking->departure) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Destination :</span>
                        <span class="detail-value">' . esc_html($booking->destination) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date et heure :</span>
                        <span class="detail-value">' . $travel_date . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Passagers :</span>
                        <span class="detail-value">' . $booking->number_of_passengers . ' personne(s)</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Distance :</span>
                        <span class="detail-value">' . number_format($booking->distance, 2) . ' km</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Aller-retour :</span>
                        <span class="detail-value">' . ($booking->round_trip ? 'Oui' : 'Non') . '</span>
                    </div>
                    ' . (!empty($booking->flight_number) ? '<div class="detail-row"><span class="detail-label">N° de vol :</span><span class="detail-value">' . esc_html($booking->flight_number) . '</span></div>' : '') . '
                    <div class="detail-row">
                        <span class="detail-label">Prix :</span>
                        <span class="detail-value">' . $price . '</span>
                    </div>
                </div>
                
                <div class="customer-info">
                    <h3>Informations client</h3>
                    <div class="detail-row">
                        <span class="detail-label">Nom :</span>
                        <span class="detail-value">' . esc_html($booking->client_first_name . ' ' . $booking->client_last_name) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email :</span>
                        <span class="detail-value">' . esc_html($booking->client_email) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Téléphone :</span>
                        <span class="detail-value">' . esc_html($booking->client_phone) . '</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Adresse :</span>
                        <span class="detail-value">' . esc_html($booking->client_address) . '</span>
                    </div>
                    ' . (!empty($booking->notes) ? '<div class="detail-row"><span class="detail-label">Notes :</span><span class="detail-value">' . esc_html($booking->notes) . '</span></div>' : '') . '
                </div>
                
                <p><a href="' . admin_url('admin.php?page=travel-booking-bookings&action=view&id=' . $booking->id) . '" style="display:inline-block;background:#d3b27f;color:white;padding:12px 20px;text-decoration:none;border-radius:5px;font-weight:bold;">Voir la réservation</a></p>
            </div>
            
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . $company_name . ' - Tous droits réservés</p>
                ' . $footer_html . '
            </div>
        </div>
    </body>
    </html>';
}
}

// Initialiser le système d'emails
Travel_Booking_Emails::init();