#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
    echo "Skipping Couchbase on HHVM"
    exit 0
fi

pecl channel-update pecl.php.net

# install this version
VERSION=2.0.7

pecl install -f couchbase-${VERSION}

#echo "extension = couchbase.so" >> /etc/php5/fpm/conf.d/couchbase.ini
#echo "extension = couchbase.so" >> /etc/php5/cli/conf.d/couchbase.ini
echo "extension = couchbase.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Create test bucket
docker exec -it couchbase_3 bash -c '/opt/couchbase/bin/couchbase-cli bucket-create -c 127.0.0.1:8091 --bucket=default --bucket-type=memcached --bucket-ramsize=64 --enable-flush=1 -u demo -p demo'