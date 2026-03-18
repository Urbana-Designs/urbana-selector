<?php
namespace Urbana\GuildLedger;

class GuildLedgerAPI {
	
	private $db_manager;
	
	public function __construct() {
		$this->db_manager = new GuildLedgerManager();
		add_action('rest_api_init', array($this, 'register_routes'));
	}
	
	public function register_routes() {
		// Get all entries
		register_rest_route('urbana/v1', '/guild-ledger', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_entries'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));
		
		// Create entry
		register_rest_route('urbana/v1', '/guild-ledger', array(
			'methods' => 'POST',
			'callback' => array($this, 'create_entry'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));
		
		// Update entry
		register_rest_route('urbana/v1', '/guild-ledger/(?P<id>\d+)', array(
			'methods' => 'PATCH',
			'callback' => array($this, 'update_entry'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));
		
		// Delete entry
		register_rest_route('urbana/v1', '/guild-ledger/(?P<id>\d+)', array(
			'methods' => 'DELETE',
			'callback' => array($this, 'delete_entry'),
			'permission_callback' => array($this, 'check_admin_permission'),
		));
	}
	
	public function get_entries($request) {
		$args = array(
			'lead_status' => $request->get_param('lead_status'),
			'interaction_type' => $request->get_param('interaction_type'),
			'limit' => $request->get_param('limit') ?: 25,
			'offset' => $request->get_param('offset') ?: 0,
		);
		
		$entries = $this->db_manager->get_ledger_entries($args);
		return new \WP_REST_Response($entries);
	}
	
	public function create_entry($request) {
		$data = $request->get_json_params();
		
		$result = $this->db_manager->insert_ledger_entry($data);
		
		if ($result === false) {
			return new \WP_Error('insert_failed', 'Failed to create entry', array('status' => 500));
		}
		
		return new \WP_REST_Response(array(
			'success' => true,
			'id' => $this->db_manager->wpdb->insert_id,
		), 201);
	}
	
	public function update_entry($request) {
		$id = $request->get_param('id');
		$data = $request->get_json_params();
		
		$result = $this->db_manager->update_ledger_entry($id, $data);
		
		if ($result === false) {
			return new \WP_Error('update_failed', 'Failed to update entry', array('status' => 500));
		}
		
		return new \WP_REST_Response(array('success' => true));
	}
	
	public function delete_entry($request) {
		$id = $request->get_param('id');
		
		$result = $this->db_manager->delete_ledger_entry($id);
		
		if ($result === false) {
			return new \WP_Error('delete_failed', 'Failed to delete entry', array('status' => 500));
		}
		
		return new \WP_REST_Response(array('success' => true));
	}
	
	public function check_admin_permission() {
		return current_user_can('manage_options');
	}
}
