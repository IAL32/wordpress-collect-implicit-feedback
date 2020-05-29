<?php

class Coimf_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $mActions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $mActions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $mFilters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $mFilters;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->mActions = array();
		$this->mFilters = array();

	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $aHook             The name of the WordPress action that is being registered.
	 * @param    object               $aComponent        A reference to the instance of the object on which the action is defined.
	 * @param    string               $aCallback         The name of the function definition on the $component.
	 * @param    int                  $aPriority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $aAcceptedArgs    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function addAction( $aHook, $aComponent, $aCallback, $aPriority = 10, $aAcceptedArgs = 1 ) {
		$this->mActions = $this->add( $this->mActions, $aHook, $aComponent, $aCallback, $aPriority, $aAcceptedArgs );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string               $aHook             The name of the WordPress filter that is being registered.
	 * @param    object               $aComponent        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $aCallback         The name of the function definition on the $component.
	 * @param    int                  $aPriority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int                  $aAcceptedArgs    Optional. The number of arguments that should be passed to the $callback. Default is 1
	 */
	public function addFilter( $aHook, $aComponent, $aCallback, $aPriority = 10, $aAcceptedArgs = 1 ) {
		$this->mFilters = $this->add( $this->mFilters, $aHook, $aComponent, $aCallback, $aPriority, $aAcceptedArgs );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array                $aHooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string               $aHook             The name of the WordPress filter that is being registered.
	 * @param    object               $aComponent        A reference to the instance of the object on which the filter is defined.
	 * @param    string               $aCallback         The name of the function definition on the $component.
	 * @param    int                  $aPriority         The priority at which the function should be fired.
	 * @param    int                  $aAcceptedArgs    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function add( $aHooks, $aHook, $aComponent, $aCallback, $aPriority, $aAcceptedArgs ) {

		$aHooks[] = array(
			'hook'          => $aHook,
			'component'     => $aComponent,
			'callback'      => $aCallback,
			'priority'      => $aPriority,
			'accepted_args' => $aAcceptedArgs
		);

		return $aHooks;

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->mFilters as $vHook ) {
			add_filter( $vHook['hook'], array( $vHook['component'], $vHook['callback'] ), $vHook['priority'], $vHook['accepted_args'] );
		}

		foreach ( $this->mActions as $vHook ) {
			add_action( $vHook['hook'], array( $vHook['component'], $vHook['callback'] ), $vHook['priority'], $vHook['accepted_args'] );
		}

	}

}
