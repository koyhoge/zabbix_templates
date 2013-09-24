<?php
// zdhcpd.php
//  dhcpd pool for zabbix

function getInput()
{
    $path = '/tmp/dhcpd.stats';
    $buf = file_get_contents($path);

//    $buf = `/usr/local/bin/dhcpd-pool`;
    $lines = explode("\n", $buf);
    return array_filter($lines);
}

function discoveryData($data)
{
    $result = array();
    foreach ($data as $item) {
        $result[] = array(
	    '{#BLOCKNAME}' => $item['block'],
        );
    }
    return array('data' => $result);
}

function getValue($data, $block, $type)
{
    $result = null;
    foreach ($data as $item) {
        if ($item['block'] === $block) {
	    $result = $item[$type];
	    break;
	}
    }
    return $result;
}

function getAvailable($data, $block)
{
    $result = null;
    foreach ($data as $item) {
        if ($item['block'] === $block) {
            $result = $item['range'] - $item['active'];
            break;
        }
    }
    return $result;
}

try {
    if ($argc === 1) {
       die('few argument');
    }

    $input = getInput();

    $data = array();
    foreach ($input as $item) {
        list($block, $active, $range) = explode(' ', $item);
	$data[] = array(
	    'block' => $block,
	    'active' => $active,
	    'range' => $range,
	);
    }
    $cmd = $argv[1];
    switch ($cmd) {
    case 'discovery':
        $out = discoveryData($data);
        echo json_encode($out);
        break;
    case 'active':
    case 'range': 
        $block = $argv[2];
	echo getValue($data, $block, $cmd);
        break;
    case 'available':
        $block = $argv[2];
        echo getAvailable($data, $block);
        break;
    }
    
} catch (Exception $e) {
    die($e->getMessage());
}

