<?php
	/*
		Class: BigTree\PendingChange
			Provides an interface for handling BigTree pending changes.
	*/

	namespace BigTree;

	use BigTree;
	use BigTreeCMS;

	class PendingChange extends BaseObject {

		static $Table = "bigtree_pending_changes";

		protected $Date;
		protected $ID;

		public $Changes;
		public $ItemID;
		public $ManyToManyChanges;
		public $Module;
		public $PendingPageParent;
		public $PublishHook;
		public $Table;
		public $TagsChanges;
		public $Title;
		public $User;

		/*
			Constructor:
				Builds a PendingChange object referencing an existing database entry.

			Parameters:
				change - Either an ID (to pull a record) or an array (to use the array as the record)
		*/

		function __construct($change) {
			// Passing in just an ID
			if (!is_array($change)) {
				$change = BigTreeCMS::$DB->fetch("SELECT * FROM bigtree_pending_changes WHERE id = ?", $change);
			}

			// Bad data set
			if (!is_array($change)) {
				trigger_error("Invalid ID or data set passed to constructor.", E_WARNING);
			} else {
				$this->Date = $change["date"];
				$this->ID = $change["id"];

				$this->Changes = (array) @json_decode($change["changes"],true);
				$this->ItemID = ($change["item_id"] !== null) ? $change["item_id"] : null;
				$this->ManyToManyChanges = (array) @json_decode($change["mtm_changes"],true);
				$this->Module = $change["module"];
				$this->PendingPageParent = $change["pending_page_parent"];
				$this->PublishHook = $change["publish_hook"];
				$this->Table = $change["table"];
				$this->TagsChanges = (array) @json_decode($change["tags_changes"],true);
				$this->Title = $change["title"];
				$this->User = $change["user"];
			}
		}

		/*
			Function: createPendingChange
				Creates a pending change.

			Parameters:
				table - The table the change applies to.
				item_id - The entry the change applies to's id.
				changes - The changes to the fields in the entry.
				mtm_changes - Many to Many changes.
				tags_changes - Tags changes.
				module - The module id for the change.

			Returns:
				A PendingChange object.
		*/

		static function create($table,$item_id,$changes,$mtm_changes = array(),$tags_changes = array(),$module = 0) {
			global $admin;

			// Get the user creating the change
			if (get_class($admin) == "BigTreeAdmin" && $admin->ID) {
				$user = $admin->ID;
			} else {
				$user = null;
			}

			$id = BigTreeCMS::$DB->insert("bigtree_pending_changes",array(
				"user" => $user,
				"date" => "NOW()",
				"table" => $table,
				"item_id" => ($item_id !== false ? $item_id : null),
				"changes" => $changes,
				"mtm_changes" => $mtm_changes,
				"tags_changes" => $tags_changes,
				"module" => $module
			));

			AuditTrail::track($table,"p".$id,"created-pending");

			return new PendingChange($id);
		}

		/*
			Function: createPage
				Creates a pending page entry.

			Parameters:
				nav_title

			Returns:
				The id of the pending change.
		*/

		function createPage($trunk,$parent,$in_nav,$nav_title,$title,$route,$meta_description,$seo_invisible,$template,$external,$new_window,$fields,$publish_at,$expire_at,$max_age,$tags = array()) {
			global $admin;

			// Get the user creating the change
			if (get_class($admin) == "BigTreeAdmin" && $admin->ID) {
				$user = $admin->ID;
			} else {
				$user = null;
			}

			$changes = array(
				"trunk" => $trunk ? "on" : "",
				"parent" => $parent,
				"in_nav" => $in_nav ? "on" : "",
				"nav_title" => BigTree::safeEncode($nav_title),
				"title" => BigTree::safeEncode($title),
				"route" => BigTree::safeEncode($route),
				"meta_description" => BigTree::safeEncode($meta_description),
				"seo_invisible" => $seo_invisible ? "on" : "",
				"template" => $template,
				"external" => $external ? Link::encode($external) : "",
				"new_window" => $new_window ? "on" : "",
				"resources" => $fields,
				"publish_at" => $publish_at ? date("Y-m-d H:i:s",strtotime($publish_at)) : null,
				"expire_at" => $publish_at ? date("Y-m-d H:i:s",strtotime($publish_at)) : null,
				"max_age" => $max_age ? intval($max_age) : ""
			);

			$id = BigTreeCMS::$DB->insert("bigtree_pending_changes",array(
				"user" => $user,
				"date" => "NOW()",
				"table" => "bigtree_pages",
				"changes" => $changes,
				"tags_changes" => $tags,
				"pending_page_parent" => intval($parent)
			));

			AuditTrail::track("bigtree_pages","p".$id,"created-pending");

			return new PendingChange($id);
		}

		/*
			Function: getEditLink
				Returns a link to where the pending change can be edited.

			Returns:
				A string containing a link to the admin.
		*/

		function getEditLink() {
			global $bigtree;

			// Pages are easy
			if ($this->Table == "bigtree_pages") {
				if ($this->ItemID) {
					return $bigtree["config"]["admin_root"]."pages/edit/".$this->ItemID."/";
				} else {
					return $bigtree["config"]["admin_root"]."pages/edit/p".$this->ID."/";
				}
			}

			// Find a form that uses this table (it's our best guess here)
			$form_id = BigTreeCMS::$DB->fetchSingle("SELECT id FROM bigtree_module_interfaces 
													 WHERE `type` = 'form' AND `table` = ?", $this->Table);
			if (!$form_id) {
				return false;
			}

			// Get the module route
			$module_route = BigTreeCMS::$DB->fetchSingle("SELECT route FROM bigtree_modules WHERE `id` = ?", $this->Module);
			
			// We set in_nav to empty because edit links aren't in nav (and add links are) so we can predict where the edit action will be this way
			$action_route = BigTreeCMS::$DB->fetchSingle("SELECT route FROM bigtree_module_actions 
														  WHERE `interface` = ? AND `in_nav` = ''", $form_id);

			// Got an action
			if ($action_route) {
				return $bigtree["config"]["admin_root"].$module_route."/".$action_route."/".($this->ItemID ?: "p".$this->ID)."/";
			}

			// Couldn't find a link
			return false;
		}
		
	}