<?php

define('COUCHBASE_VAL_MASK', 0x1F);
define('COUCHBASE_VAL_IS_STRING', 0);
define('COUCHBASE_VAL_IS_LONG', 1);
define('COUCHBASE_VAL_IS_DOUBLE', 2);
define('COUCHBASE_VAL_IS_BOOL', 3);
define('COUCHBASE_VAL_IS_SERIALIZED', 4);
define('COUCHBASE_VAL_IS_IGBINARY', 5);
define('COUCHBASE_VAL_IS_JSON', 6);
define('COUCHBASE_COMPRESSION_MASK', 0x7 << 5);
define('COUCHBASE_COMPRESSION_NONE', 0 << 5);
define('COUCHBASE_COMPRESSION_ZLIB', 1 << 5);
define('COUCHBASE_COMPRESSION_FASTLZ', 2 << 5);
define('COUCHBASE_COMPRESSION_MCISCOMPRESSED', 1 << 4);
define('COUCHBASE_SERTYPE_JSON', 0);
define('COUCHBASE_SERTYPE_IGBINARY', 1);
define('COUCHBASE_SERTYPE_PHP', 2);
define('COUCHBASE_CMPRTYPE_NONE', 0);
define('COUCHBASE_CMPRTYPE_ZLIB', 1);
define('COUCHBASE_CMPRTYPE_FASTLZ', 2);
define('COUCHBASE_CFFMT_MASK', 0xFF << 24);
define('COUCHBASE_CFFMT_PRIVATE', 1 << 24);
define('COUCHBASE_CFFMT_JSON', 2 << 24);
define('COUCHBASE_CFFMT_RAW', 3 << 24);
define('COUCHBASE_CFFMT_STRING', 4 << 24);

/**
 * File for the CouchbaseBucket class.
 *
 * @author Brett Lawson <brett19@gmail.com>
 */

/**
 * Represents a bucket connection.
 *
 * Note: This class must be constructed by calling the openBucket
 * method of the CouchbaseCluster class.
 *
 * @property integer $operationTimeout
 * @property integer $viewTimeout
 * @property integer $durabilityInterval
 * @property integer $durabilityTimeout
 * @property integer $httpTimeout
 * @property integer $configTimeout
 * @property integer $configDelay
 * @property integer $configNodeTimeout
 * @property integer $htconfigIdleTimeout
 *
 * @package Couchbase
 *
 * @see CouchbaseCluster::openBucket()
 */
class CouchbaseBucket {
    /**
     * @var _CouchbaseBucket
     * @ignore
     *
     * Pointer to our C binding backing class.
     */
    private $me;

    /**
     * @var string
     * @ignore
     *
     * The name of the bucket this object represents.
     */
    private $name;

    /**
     * @var _CouchbaseCluster
     * @ignore
     *
     * Pointer to a manager instance if there is one.
     */
    private $_manager;

    /**
     * @var array
     * @ignore
     *
     * A list of N1QL nodes to query.
     */
    private $queryhosts = NULL;

    /**
     * Constructs a bucket connection.
     *
     * @private
     * @ignore
     *
     * @param string $dsn A cluster DSN to connect with.
     * @param string $name The name of the bucket to connect to.
     * @param string $password The password to authenticate with.
     *
     * @private
     */
    public function __construct($dsn, $name, $password) {
        $this->me = new _CouchbaseBucket($dsn, $name, $password);
        $this->me->setTranscoder("couchbase_default_encoder", "couchbase_default_decoder");
        $this->name = $name;
    }

    /**
     * Returns an instance of a CouchbaseBucketManager for performing management
     * operations against a bucket.
     *
     * @return CouchbaseBucketManager
     */
    public function manager() {
        if (!$this->_manager) {
            $this->_manager = new CouchbaseBucketManager(
                $this->me, $this->name);
        }
        return $this->_manager;
    }

    /**
     * Enables N1QL support on the client.  A cbq-server URI must be passed.
     * This method will be deprecated in the future in favor of automatic
     * configuration through the connected cluster.
     *
     * @param $hosts An array of host/port combinations which are N1QL servers
     * attached to the cluster.
     */
    public function enableN1ql($hosts) {
        if (is_array($hosts)) {
            $this->queryhosts = $hosts;
        } else {
            $this->queryhosts = array($hosts);
        }
    }

    /**
     * Inserts a document.  This operation will fail if
     * the document already exists on the cluster.
     *
     * @param string|array $ids
     * @param mixed $val
     * @param array $options expiry,flags
     * @return mixed
     */
    public function insert($ids, $val = NULL, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->insert($ids, $val, $options));
    }

    /**
     * Inserts or updates a document, depending on whether the
     * document already exists on the cluster.
     *
     * @param string|array $ids
     * @param mixed $val
     * @param array $options expiry,flags
     * @return mixed
     */
    public function upsert($ids, $val = NULL, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->upsert($ids, $val, $options));
    }

    /**
     * Replaces a document.
     *
     * @param string|array $ids
     * @param mixed $val
     * @param array $options cas,expiry,flags
     * @return mixed
     */
    public function replace($ids, $val = NULL, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->replace($ids, $val, $options));
    }

    /**
     * Appends content to a document.
     *
     * @param string|array $ids
     * @param mixed $val
     * @param array $options cas
     * @return mixed
     */
    public function append($ids, $val = NULL, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->append($ids, $val, $options));
    }

    /**
     * Prepends content to a document.
     *
     * @param string|array $ids
     * @param mixed $val
     * @param array $options cas
     * @return mixed
     */
    public function prepend($ids, $val = NULL, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->prepend($ids, $val, $options));
    }

    /**
     * Deletes a document.
     *
     * @param string|array $ids
     * @param array $options cas
     * @return mixed
     */
    public function remove($ids, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->remove($ids, $options));
    }

    /**
     * Retrieves a document.
     *
     * @param string|array $ids
     * @param array $options lock
     * @return mixed
     */
    public function get($ids, $options = array()) {
        return $this->me->get($ids, $options);
    }

    /**
     * Retrieves a document and simultaneously updates its expiry.
     *
     * @param string $id
     * @param integer $expiry
     * @param array $options
     * @return mixed
     */
    public function getAndTouch($id, $expiry, $options = array()) {
        $options['expiry'] = $expiry;
        return $this->me->get($id, $options);
    }

    /**
     * Retrieves a document and locks it.
     *
     * @param string $id
     * @param integer $lockTime
     * @param array $options
     * @return mixed
     */
    public function getAndLock($id, $lockTime, $options = array()) {
        $options['lockTime'] = $lockTime;
        return $this->me->get($id, $options);
    }

    /**
     * Retrieves a document from a replica.
     *
     * @param string $id
     * @param array $options
     * @return mixed
     */
    public function getFromReplica($id, $options = array()) {
        return $this->me->getFromReplica($id, $options);
    }

    /**
     * Updates a documents expiry.
     *
     * @param string $id
     * @param integer $expiry
     * @param array $options
     * @return mixed
     */
    public function touch($id, $expiry, $options = array()) {
        return $this->me->touch($id, $expiry, $options);
    }

    /**
     * Increment or decrements a key (based on $delta).
     *
     * @param string|array $ids
     * @param integer $delta
     * @param array $options initial,expiry
     * @return mixed
     */
    public function counter($ids, $delta, $options = array()) {
        return $this->_endure($ids, $options,
            $this->me->counter($ids, $delta, $options));
    }

    /**
     * Unlocks a key previous locked with a call to get().
     * @param string|array $ids
     * @param array $options cas
     * @return mixed
     */
    public function unlock($ids, $options = array()) {
        return $this->me->unlock($ids, $options);
    }

    /**
     * Executes a view query.
     *
     * @param ViewQuery $queryObj
     * @return mixed
     * @throws CouchbaseException
     *
     * @internal
     */
    public function _view($queryObj) {
        $path = $queryObj->toString();
        $res = $this->me->http_request(1, 1, $path, NULL, 1);
        $out = json_decode($res, true);
        if (isset($out['error'])) {
            throw new CouchbaseException($out['error'] . ': ' . $out['reason']);
        }
        return $out;
    }

    /**
     * Performs a N1QL query.
     *
     * @param $dmlstring
     * @return mixed
     * @throws CouchbaseException
     *
     * @internal
     */
    public function _n1ql($queryObj) {
        $data = json_encode($queryObj->toObject());

        if ($this->queryhosts) {
            $hostidx = array_rand($this->queryhosts, 1);
            $host = $this->queryhosts[$hostidx];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://' . $host . '/query');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data))
            );
            $res = curl_exec($ch);
            curl_close($ch);
        } else {
            $res = $this->me->http_request(3, 2, NULL, $data, 1);
        }

        $resjson = json_decode($res, true);

        if (isset($resjson['errors'])) {
            throw new CouchbaseException($resjson['errors'][0]['msg'], 999);
        }

        return $resjson['results'];
    }

    /**
     * Performs a query (either ViewQuery or N1qlQuery).
     *
     * @param CouchbaseQuery $query
     * @return mixed
     * @throws CouchbaseException
     */
    public function query($query) {
        if ($query instanceof _CouchbaseDefaultViewQuery ||
            $query instanceof _CouchbaseSpatialViewQuery) {
            return $this->_view($query);
        } else if ($query instanceof CouchbaseN1qlQuery) {
            return $this->_n1ql($query);
        } else {
            throw new CouchbaseException(
                'Passed object must be of type ViewQuery or N1qlQuery');
        }
    }

    /**
     * Sets custom encoder and decoder functions for handling serialization.
     *
     * @param string $encoder The encoder function name
     * @param string $decoder The decoder function name
     */
    public function setTranscoder($encoder, $decoder) {
        return $this->me->setTranscoder($encoder, $decoder);
    }

    /**
     * Ensures durability requirements are met for an executed
     *  operation.  Note that this function will automatically
     *  determine the result types and check for any failures.
     *
     * @private
     * @ignore
     *
     * @param $id
     * @param $res
     * @param $options
     * @return mixed
     * @throws Exception
     */
    private function _endure($id, $options, $res) {
        if ((!isset($options['persist_to']) || !$options['persist_to']) &&
            (!isset($options['replicate_to']) || !$options['replicate_to'])) {
            return $res;
        }
        if (is_array($res)) {
            // Build list of keys to check
            $chks = array();
            foreach ($res as $key => $result) {
                if (!$result->error) {
                    $chks[$key] = array(
                        'cas' => $result->cas
                    );
                }
            }

            // Do the checks
            $dres = $this->me->durability($chks, array(
                'persist_to' => $options['persist_to'],
                'replicate_to' => $options['replicate_to']
            ));

            // Copy over the durability errors
            foreach ($dres as $key => $result) {
                if (!$result) {
                    $res[$key]->error = $result->error;
                }
            }

            return $res;
        } else {
            if ($res->error) {
                return $res;
            }

            $dres = $this->me->durability($id, array(
                'cas' => $res->cas,
                'persist_to' => $options['persist_to'],
                'replicate_to' => $options['replicate_to']
            ));

            return $res;
        }
    }

    /**
     * Magic function to handle the retrieval of various properties.
     *
     * @internal
     */
    public function __get($name) {
        if ($name == 'operationTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_OP_TIMEOUT);
        } else if ($name == 'viewTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_VIEW_TIMEOUT);
        } else if ($name == 'durabilityInterval') {
            return $this->me->getOption(COUCHBASE_CNTL_DURABILITY_INTERVAL);
        } else if ($name == 'durabilityTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_DURABILITY_TIMEOUT);
        } else if ($name == 'httpTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_HTTP_TIMEOUT);
        } else if ($name == 'configTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_CONFIGURATION_TIMEOUT);
        } else if ($name == 'configDelay') {
            return $this->me->getOption(COUCHBASE_CNTL_CONFDELAY_THRESH);
        } else if ($name == 'configNodeTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_CONFIG_NODE_TIMEOUT);
        } else if ($name == 'htconfigIdleTimeout') {
            return $this->me->getOption(COUCHBASE_CNTL_HTCONFIG_IDLE_TIMEOUT);
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**
     * Magic function to handle the setting of various properties.
     *
     * @internal
     */
    public function __set($name, $value) {
        if ($name == 'operationTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_OP_TIMEOUT, $value);
        } else if ($name == 'viewTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_VIEW_TIMEOUT, $value);
        } else if ($name == 'durabilityInterval') {
            return $this->me->setOption(COUCHBASE_CNTL_DURABILITY_INTERVAL, $value);
        } else if ($name == 'durabilityTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_DURABILITY_TIMEOUT, $value);
        } else if ($name == 'httpTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_HTTP_TIMEOUT, $value);
        } else if ($name == 'configTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_CONFIGURATION_TIMEOUT, $value);
        } else if ($name == 'configDelay') {
            return $this->me->setOption(COUCHBASE_CNTL_CONFDELAY_THRESH, $value);
        } else if ($name == 'configNodeTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_CONFIG_NODE_TIMEOUT, $value);
        } else if ($name == 'htconfigIdleTimeout') {
            return $this->me->setOption(COUCHBASE_CNTL_HTCONFIG_IDLE_TIMEOUT, $value);
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __set(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
}


/**
 * Various transcoder functions that are packaged by default with the
 * PHP SDK.
 *
 * @author Brett Lawson <brett19@gmail.com>
 */

/**
 * The default options for V1 encoding when using the default
 * transcoding functionality.
 * @internal
 */
$COUCHBASE_DEFAULT_ENCOPTS = array(
    'sertype' => COUCHBASE_SERTYPE_JSON,
    'cmprtype' => COUCHBASE_CMPRTYPE_NONE,
    'cmprthresh' => 0,
    'cmprfactor' => 0
);

/**
 * The default options from past versions of the PHP SDK.
 * @internal
 */
$COUCHBASE_OLD_ENCOPTS = array(
    'sertype' => COUCHBASE_SERTYPE_PHP,
    'cmprtype' => COUCHBASE_CMPRTYPE_NONE,
    'cmprthresh' => 2000,
    'cmprfactor' => 1.3
);

/**
 * The default options for V1 decoding when using the default
 * transcoding functionality.
 * @internal
 */
$COUCHBASE_DEFAULT_DECOPTS = array(
    'jsonassoc' => false
);

/**
 * Performs encoding of user provided types into binary form for
 * on the server according to the original PHP SDK specification.
 *
 *
 * @param mixed $value  The value passed by the user
 * @param array $options Various encoding options
 * @return array An array specifying the bytes, flags and datatype to store
 */
function couchbase_basic_encoder_v1($value, $options) {
    $data = NULL;
    $flags = 0;
    $datatype = 0;

    $sertype = $options['sertype'];
    $cmprtype = $options['cmprtype'];
    $cmprthresh = $options['cmprthresh'];
    $cmprfactor = $options['cmprfactor'];

    $vtype = gettype($value);
    if ($vtype == 'string') {
        $flags = COUCHBASE_VAL_IS_STRING | COUCHBASE_CFFMT_STRING;
        $data = $value;
    } else if ($vtype == 'integer') {
        $flags = COUCHBASE_VAL_IS_LONG | COUCHBASE_CFFMT_JSON;
        $data = (string)$value;
        $cmprtype = COUCHBASE_CMPRTYPE_NONE;
    } else if ($vtype == 'double') {
        $flags = COUCHBASE_VAL_IS_DOUBLE | COUCHBASE_CFFMT_JSON;
        $data = (string)$value;
        $cmprtype = COUCHBASE_CMPRTYPE_NONE;
    } else if ($vtype == 'boolean') {
        $flags = COUCHBASE_VAL_IS_BOOL | COUCHBASE_CFFMT_JSON;
        $data = $value ? 'true' : 'false';
        $cmprtype = COUCHBASE_CMPRTYPE_NONE;
    } else {
        if ($sertype == COUCHBASE_SERTYPE_JSON) {
            $flags = COUCHBASE_VAL_IS_JSON | COUCHBASE_CFFMT_JSON;
            $data = json_encode($value);
        } else if ($sertype == COUCHBASE_SERTYPE_IGBINARY) {
            $flags = COUCHBASE_VAL_IS_IGBINARY | COUCHBASE_CFFMT_PRIVATE;
            $data = igbinary_serialize($value);
        } else if ($sertype == COUCHBASE_SERTYPE_PHP) {
            $flags = COUCHBASE_VAL_IS_SERIALIZED | COUCHBASE_CFFMT_PRIVATE;
            $data = serialize($value);
        }
    }

    if (strlen($data) < $cmprthresh) {
        $cmprtype = COUCHBASE_CMPRTYPE_NONE;
    }

    if ($cmprtype != COUCHBASE_CMPRTYPE_NONE) {
        $cmprdata = NULL;
        $cmprflags = COUCHBASE_COMPRESSION_NONE;

        if ($cmprtype == COUCHBASE_CMPRTYPE_ZLIB) {
            $cmprdata = gzencode($data);
            $cmprflags = COUCHBASE_COMPRESSION_ZLIB;
        } else if ($cmprtype == COUCHBASE_CMPRTYPE_FASTLZ) {
            $cmprdata = fastlz_compress($data);
            $cmprflags = COUCHBASE_COMPRESSION_FASTLZ;
        }

        if ($cmprdata != NULL) {
            if (strlen($data) > strlen($cmprdata) * $cmprfactor) {
                $data = $cmprdata;
                $flags |= $cmprflags;
                $flags |= COUCHBASE_COMPRESSION_MCISCOMPRESSED;

                $flags &= ~COUCHBASE_CFFMT_MASK;
                $flags |= COUCHBASE_CFFMT_PRIVATE;
            }
        }
    }

    return array($data, $flags, $datatype);
}

/**
 * Performs decoding of the server provided binary data into
 * PHP types according to the original PHP SDK specification.
 *
 *
 * @param string $bytes The binary received from the server
 * @param int $flags The flags received from the server
 * @param int $datatype The datatype received from the server
 * @param array $options
 * @return mixed The resulting decoded value
 *
 * @throws CouchbaseException
 */
function couchbase_basic_decoder_v1($bytes, $flags, $datatype, $options) {
    $cffmt = $flags & COUCHBASE_CFFMT_MASK;
    $sertype = $flags & COUCHBASE_VAL_MASK;
    $cmprtype = $flags & COUCHBASE_COMPRESSION_MASK;

    $data = $bytes;
    if ($cffmt != 0 && $cffmt != COUCHBASE_CFFMT_PRIVATE) {
        if ($cffmt == COUCHBASE_CFFMT_JSON) {
            $retval = json_decode($data, $options['jsonassoc']);
        } else if ($cffmt == COUCHBASE_CFFMT_RAW) {
            $retval = $data;
        } else if ($cffmt == COUCHBASE_CFFMT_STRING) {
            $retval = (string)$data;
        } else {
            throw new CouchbaseException("Unknown flags value -- cannot decode value");
        }
    } else {
        if ($cmprtype == COUCHBASE_COMPRESSION_ZLIB) {
            $bytes = gzdecode($bytes);
        } else if ($cmprtype == COUCHBASE_COMPRESSION_FASTLZ) {
            $data = fastlz_decompress($bytes);
        }

        $retval = NULL;
        if ($sertype == COUCHBASE_VAL_IS_STRING) {
            $retval = $data;
        } else if ($sertype == COUCHBASE_VAL_IS_LONG) {
            $retval = intval($data);
        } else if ($sertype == COUCHBASE_VAL_IS_DOUBLE) {
            $retval = floatval($data);
        } else if ($sertype == COUCHBASE_VAL_IS_BOOL) {
            $retval = $data != "";
        } else if ($sertype == COUCHBASE_VAL_IS_JSON) {
            $retval = json_decode($data, $options['jsonassoc']);
        } else if ($sertype == COUCHBASE_VAL_IS_IGBINARY) {
            $retval = igbinary_unserialize($data);
        } else if ($sertype == COUCHBASE_VAL_IS_SERIALIZED) {
            $retval = unserialize($data);
        }
    }

    return $retval;
}

/**
 * Default passthru encoder which simply passes data
 * as-is rather than performing any transcoding.
 */
function couchbase_passthru_encoder($value) {
    return array($value, 0, 0);
}

/**
 * Default passthru encoder which simply passes data
 * as-is rather than performing any transcoding.
 */
function couchbase_passthru_decoder($bytes, $flags, $datatype) {
    return $bytes;
}

/**
 * The default encoder for the client.  Currently invokes the
 * v1 encoder directly with the default set of encoding options.
 */
function couchbase_default_encoder($value) {
    global $COUCHBASE_DEFAULT_ENCOPTS;
    return couchbase_basic_encoder_v1($value, $COUCHBASE_DEFAULT_ENCOPTS);
}

/**
 * The default decoder for the client.  Currently invokes the
 */
function couchbase_default_decoder($bytes, $flags, $datatype) {
    global $COUCHBASE_DEFAULT_DECOPTS;
    return couchbase_basic_decoder_v1($bytes, $flags, $datatype, $COUCHBASE_DEFAULT_DECOPTS);
}
