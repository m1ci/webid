<?php
/**
 * ARC2 JSON-LD Serializer
 *
 * @author John Walker <john.walker@semaku.com>
 * @license W3C Software License and GPL
 * @homepage <https://github.com/semsol/arc2>
 * @package ARC2
*/

ARC2::inc('RDFSerializer');

class ARC2_JSONLDSerializer extends ARC2_RDFSerializer {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function __init() {
    parent::__init();
    $this->content_header = 'application/ld+json';
  }

  /*  */
  
  function getTerm($v, $term = 's') {
    if (!is_array($v)) {
      if (preg_match('/^\_\:/', $v)) {
        return ($term == 'o') ? $this->getTerm(array('value' => $v, 'type' => 'bnode'), 'o') : '"' . $v . '"';
      }
      return ($term == 'o') ? $this->getTerm(array('value' => $v, 'type' => 'uri'), 'o') : '"' . $v . '"';
    }
    if (!isset($v['type']) || ($v['type'] != 'literal')) {
      if ($term != 'o') {
        return $this->getTerm($v['value'], $term);
      }
      return '{ "@id" : "' . $this->jsonEscape($v['value']) . '" }';
    }
    /* literal */
    $r = '{ "@value" : "' . $this->jsonEscape($v['value']) . '"';
    $suffix = isset($v['datatype']) ? ', "@type" : "' . $v['datatype'] . '"' : '';
    $suffix = isset($v['lang']) ? ', "@language" : "' . $v['lang'] . '"' : $suffix;
    $r .= $suffix . ' }';
    return $r;
  }

  function jsonEscape($v) {
    if (function_exists('json_encode')) {
        return preg_replace('/^"(.*)"$/', '\\1', str_replace("\/","/",json_encode($v)));
    }
    $from = array("\\", "\r", "\t", "\n", '"', "\b", "\f");
    $to = array('\\\\', '\r', '\t', '\n', '\"', '\b', '\f');
    return str_replace($from, $to, $v);
  }
    
  function getSerializedIndex($index, $raw = 0) {
    $r = '';
    $nl = "\n";
    foreach ($index as $s => $ps) {
      $r .= $r ? ',' . $nl . $nl : '';
      $r .= '  { '. $nl . '    "@id" : ' . $this->getTerm($s);
      //$first_p = 1;
      foreach ($ps as $p => $os) {
        $r .= ',' . $nl;
        $r .= '    ' . $this->getTerm($p). ' : [';
        $first_o = 1;
        if (!is_array($os)) {/* single literal o */
          $os = array(array('value' => $os, 'type' => 'literal'));
        }
        foreach ($os as $o) {
          $r .= $first_o ? $nl : ',' . $nl;
          $r .= '      ' . $this->getTerm($o, 'o');
          $first_o = 0;
        }
        $r .= $nl . '    ]';
      }
      $r .= $nl . '  }';
    }
    $r .= $r ? ' ' : '';
    return '[' . $nl . $r . $nl . ']';
  }
  
  /*  */

}
