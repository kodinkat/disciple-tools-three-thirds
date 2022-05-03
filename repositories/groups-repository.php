<?php

class DT_33_Groups_Repository {
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->meetings = DT_33_Meetings_Repository::instance();
    }

    public function all() {
        return DT_Posts::list_posts( 'groups', [
            'sort' => 'name'
        ] )['posts'];
    }

    /**
     * Extract the groups that have meetings
     * @param array $params
     * @return array
     */
    public function withMeetings( $params = [] ) {
        $groups = array_values( array_reduce( $this->meetings->all( $params ), function ( $groups, $meeting ) {
            if ( !$meeting['groups'] || !count( $meeting['groups'] ) ) {
                return $groups;
            }

            foreach ( $meeting['groups'] as $group ) {
                $groups[ $group['ID'] ] = $group;
            }

            return $groups;
        }, [] ) );

        //ABC order
        usort( $groups, function ( $a, $b ) {
            return strcmp( $a["post_title"], $b["post_title"] );
        } );

        return $groups;
    }

    public function create( $params )
    {
        return DT_Posts::create_post( 'groups', $params, false, false );
    }

    /**
     * Find by ID
     */
    public function find( $id ) {
        $filtered = array_filter($this->all(), function($group) use ($id) {
            return (int) $group['ID'] === (int) $id;
        });
        if (count($filtered)) {
            return array_values($filtered)[0];
        }
        return null;
    }
}
