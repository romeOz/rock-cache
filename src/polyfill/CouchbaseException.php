<?php

////////////////////////////////////////////////////////
//                                                    //
//       The following exception classes exists       //
//                                                    //
////////////////////////////////////////////////////////

/**
 * The base Couchbase Exception class which all Couchbase Exceptions
 * inherit from.
 */
class CouchbaseException extends Exception {

}

/**
 * Exception thrown when an invalid key is passed
 */
class CouchbaseIllegalKeyException extends CouchbaseException {

}

/**
 * Exception thrown when the server determines the requested key does not
 * exist.
 */
class CouchbaseNoSuchKeyException extends CouchbaseException {

}

/**
 * Exception thrown when authentication with the server fails.
 */
class CouchbaseAuthenticationException extends CouchbaseException {

}

/**
 * Exception thrown on internal libcouchbase errors.
 */
class CouchbaseLibcouchbaseException extends CouchbaseException {

}

/**
 * Exception thrown when the server encounters an error.
 */
class CouchbaseServerException extends CouchbaseException {

}

/**
 * Exception thrown when the CAS value you passed does not match the servers
 * current value.
 */
class CouchbaseKeyMutatedException extends CouchbaseException {

}

/**
 * Exception thrown when an operation times out.
 */
class CouchbaseTimeoutException extends CouchbaseException {

}

/**
 * Exception thrown when there are not enough nodes online to preform a
 * particular operation.  Generally occurs due to invalid durability
 * requirements
 */
class CouchbaseNotEnoughNodesException extends CouchbaseException {

}

/**
 * Exception thrown when an illegal option is passed to a method.
 */
class CouchbaseIllegalOptionException extends CouchbaseException {

}

/**
 * Exception thrown when an illegal value is passed to a method.
 */
class CouchbaseIllegalValueException extends CouchbaseException {

}