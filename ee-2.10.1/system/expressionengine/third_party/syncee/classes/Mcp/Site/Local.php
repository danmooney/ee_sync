<?php

class Syncee_Mcp_Site_Local
{
    public function viewLocalSiteList()
    {
        $ee_sites = ee()->db->get('sites')->result_array();

        return Syncee_View::render(__FUNCTION__, array(
            'ee_sites'           => $ee_sites,
        ));
    }
}