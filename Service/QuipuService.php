<?php
/**
 * Created by PhpStorm.
 * User: tilotiti
 * Date: 20/02/2017
 * Time: 17:35
 */

namespace Tiloweb\QuipuBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Tiloweb\QuipuBundle\Model\Contact;
use Tiloweb\QuipuBundle\Model\Invoice;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class QuipuService
{
    private $app;
    private $secret;

    const API_URL = 'https://getquipu.com/';
    const AUTH_URL = 'oauth/token';

    private $error;
    private $accessToken;

    public function __construct($quipu_app, $quipu_secret)
    {
        $this->app = $quipu_app;
        $this->secret = $quipu_secret;
    }

    private function requestAccessToken() {
        $cache = new FilesystemAdapter();

        $tokenCache = $cache->getItem('token');

        if($tokenCache->isHit()) {
            $this->accessToken = $tokenCache->get();
            return true;
        }

        $curl = curl_init( self::API_URL.self::AUTH_URL );

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->app . ":" . $this->secret);
        curl_setopt($curl, CURLOPT_HEADER,'Content-Type: application/x-www-form-urlencoded;charset=UTF-8');
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
            'grant_type' => 'client_credentials',
            'scope' => 'ecommerce'
        ) );

        $auth = curl_exec( $curl );
        $secret = json_decode($auth);
        curl_close($curl);

        if(isset($secret->error)) {
            throw new \Exception($secret->error);
        } else {
            $this->accessToken = $secret->access_token;

            $tokenCache->set($this->accessToken);
            $tokenCache->expiresAfter($secret->expires_in);

            $cache->save($tokenCache);
            return true;
        }
    }

    private function getRequestToken() {
        if(empty($this->accessToken)) {
            $this->requestAccessToken();
        }

        return $this->accessToken;
    }

    private function request($method, $endpoint, $data = array()) {
        $token = $this->getRequestToken();

        $curl = curl_init(self::API_URL.$endpoint);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$token,
            'Accept: application/vnd.quipu.v1+json',
            'Content-Type: application/vnd.quipu.v1+json'
        ));

        switch($method) {
            case "POST":
            case "PUT":
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case "GET":
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                break;
        }

        $response = curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if($response) {
            $req_json = json_decode($response);

            if(isset($req_json->errors)) {
                throw new \Exception($req_json->errors[0]->detail." => ".$req_json->errors[0]->source->pointer.' : '.json_encode($data));
            } else {
                return $req_json;
            }
        } else {
            throw new \Exception("Empty response ($http_code) : ".json_encode($data));
        }

    }

    /*
     * Contacts
     */

    /**
     * List Quipu Contact
     * @param $page int
     * @return mixed
     */
    public function listContacts($page = 1) {
        if(is_int($page)) {
            $url = '/contacts?page[number]='.$page;
        } else {
            $url = '/contacts';
        }
        $request = $this->request('GET', $url);

        $listContact = array();

        foreach($request->data as $data) {
            $contact = new Contact();
            $contact->setId($data->id);
            $contact->set($data->attributes);

            $listContact[] = $contact;
        }

        return array(
            'data' => $listContact,
            'pagination' => array(
                'current_page' => $request->meta->pagination_info->current_page,
                'total_pages' => $request->meta->pagination_info->total_pages,
                'total_results' => $request->meta->pagination_info->total_results
            )
        );
    }

    /**
     * Create a new contact in Quipu
     * @param array $data
     * @return Contact
     */
    public function createContact(array $data) {
        $request = $this->request('POST', '/contacts', array(
            "data" => array(
                "type" => 'contacts',
                "attributes" => $data
            )
        ));

        $contact = new Contact();
        $contact->setId($request->data->id);
        $contact->set($request->data->attributes);

        return $contact;
    }

    /**
     * Get a contact from Quipu
     * @param $id
     * @return Contact
     */
    public function getContact($id) {
        $request = $this->request('GET', '/contacts/'.$id);

        $contact = new Contact();
        $contact->setId($request->data->id);
        $contact->set($request->data->attributes);

        return $contact;
    }

    /**
     * Update attributes contact from Quipu
     * @param $id
     * @param array $data
     * @return Contact
     */
    public function updateContact($id, array $data) {
        $request = $this->request('PUT', '/contacts/'.$id, array(
            'data' => array(
                'type' => 'contacts',
                'attributes' => $data
            )
        ));

        $contact = new Contact();
        $contact->setId($request->data->id);
        $contact->set($request->data->attributes);

        return $contact;
    }

    /*
     * Invoices
     */

    /**
     * List all invoice from Quipu (optional : filter by a contact)
     * @param int|null $contact
     * @param int $page
     * @return array
     */
    public function listInvoices($contact = null, $page = 1) {
        // TODO : Contact Filter
        if(is_int($page)) {
            $url = '/invoices?page[number]='.$page;
        } else {
            $url = '/invoices';
        }
        $request = $this->request('GET', $url);

        $listInvoices = array();

        foreach($request->data as $data) {
            $invoice = new Invoice();
            $invoice->setId($data->id);
            $invoice->set($data->attributes);

            $listInvoices[] = $invoice;
        }

        return array(
            'data' => $listInvoices,
            'pagination' => array(
                'current_page' => $request->meta->pagination_info->current_page,
                'total_pages' => $request->meta->pagination_info->total_pages,
                'total_results' => $request->meta->pagination_info->total_results
            )
        );
    }

    /**
     * Create an invoice in Quipu
     * @param $contact
     * @param array $data
     * @param array $items
     * @return Invoice
     * @throws \Exception
     */
    public function createInvoice($contact, array $data, array $items) {
        $listItems = array();

        if(count($items) == 0) {
            throw new \Exception("At least one Item is required");
        }

        foreach($items as $item) {
            $listItems[] = array(
                'type' => 'book_entry_items',
                'attributes' => $item
            );
        }

        $request = $this->request('POST', '/invoices', array(
            "data" => array(
                "type" => 'invoices',
                "attributes" => $data,
                "relationships" => array(
                    'contact' => array(
                        'data' => array(
                            'id' => $contact,
                            'type' => 'contacts'
                        )
                    ),
                    'items' => array(
                        'data' => $listItems
                    )
                )
            )
        ));

        $invoice = new Invoice();
        $invoice->setId($request->data->id);
        $invoice->set($request->data->attributes);

        return $invoice;
    }

    /**
     * Get an Invoice from Quipu
     * @param $id int
     * @return Invoice
     */
    public function getInvoice($id) {
        $request = $this->request('GET', '/invoices/'.$id);

        $invoice = new Invoice();
        $invoice->setId($request->data->id);
        $invoice->set($request->data->attributes);

        return $invoice;
    }

    /**
     * Update attributes invoice from Quipu
     * @param $id
     * @param array $data
     * @return Invoice
     */
    public function updateInvoice($id, array $data) {
        $request = $this->request('PUT', '/invoices/'.$id, array(
            'data' => array(
                'type' => 'invoices',
                'attributes' => $data
            )
        ));

        $invoice = new Invoice();
        $invoice->setId($request->data->id);
        $invoice->set($request->data->attributes);

        return $invoice;
    }
}