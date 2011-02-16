<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

/**
 * This abstract class defines an interface for metadata storage sources.
 *
 * It also contains the overview of the different metadata storage sources.
 * A metadata storage source can be loaded by passing the configuration of it
 * to the getSource static function.
 *
 * @author Olav Morken, UNINETT AS.
 * @author Andreas Aakre Solberg, UNINETT AS.
 * @package simpleSAMLphp
 * @version $Id$
 */
abstract class SimpleSAML_Metadata_MetaDataStorageSource {


	/**
	 * This function creates a metadata source based on the given configuration.
	 * The type of source is based on the 'type' parameter in the configuration.
	 * The default type is 'flatfile'.
	 *
	 * @param $sourceConfig  Associative array with the configuration for this metadata source.
	 * @return An instance of a metadata source with the given configuration.
	 */
	public static function getSource($sourceConfig) {

		assert(is_array($sourceConfig));

		if(array_key_exists('type', $sourceConfig)) {
			$type = $sourceConfig['type'];
		} else {
			$type = 'flatfile';
		}

		switch($type) {
		case 'flatfile':
			return new SimpleSAML_Metadata_MetaDataStorageHandlerFlatFile($sourceConfig);
		case 'xml':
			return new SimpleSAML_Metadata_MetaDataStorageHandlerXML($sourceConfig);
		case 'dynamicxml':
			return new SimpleSAML_Metadata_MetaDataStorageHandlerDynamicXML($sourceConfig);
		default:
			throw new Exception('Invalid metadata source type: "' . $type . '".');
		}
	}


	/**
	 * This function attempts to generate an associative array with metadata for all entities in the
	 * given set. The key of the array is the entity id.
	 *
	 * A subclass should override this function if it is able to easily generate this list.
	 *
	 * @param $set  The set we want to list metadata for.
	 * @return An associative array with all entities in the given set, or an empty array if we are
	 *         unable to generate this list.
	 */
	public function getMetadataSet($set) {
		return array();
	}


	/**
	 * This function resolves an host/path combination to an entity id.
	 *
	 * This class implements this function using the getMetadataSet-function. A subclass should
	 * override this function if it doesn't implement the getMetadataSet function, or if the
	 * implementation of getMetadataSet is slow.
	 *
	 * @param $hostPath  The host/path combination we are looking up.
	 * @param $set  Which set of metadata we are looking it up in.
	 * @param $type Do you want to return the metaindex or the entityID. [entityid|metaindex]

	 * @return An entity id which matches the given host/path combination, or NULL if
	 *         we are unable to locate one which matches.
	 */
	public function getEntityIdFromHostPath($hostPath, $set, $type = 'entityid') {

		$metadataSet = $this->getMetadataSet($set);

		foreach($metadataSet AS $index => $entry) {

			if(!array_key_exists('host', $entry)) {
				continue;
			}

			if($hostPath === $entry['host']) {
				if ($type === 'entityid') {
					return $entry['entityid'];
				} else {
					return $index;
				}	
			}
		}

		/* No entries matched - we should return NULL. */
		return NULL;
	}
	
	/**
	 * This function will go through all the metadata, and check the hint.cidr
	 * parameter, which defines a network space (ip range) for each remote entry.
	 * This function returns the entityID for any of the entities that have an 
	 * IP range which the IP falls within.
	 *
	 * @param $set  Which set of metadata we are looking it up in.
	 * @param $ip	IP address
	 * @param $type Do you want to return the metaindex or the entityID. [entityid|metaindex]
	 * @return The entity id of a entity which have a CIDR hint where the provided
	 * 		IP address match.
	 */
	public function getPreferredEntityIdFromCIDRhint($set, $ip, $type = 'entityid') {
		
		$metadataSet = $this->getMetadataSet($set);

		foreach($metadataSet AS $index => $entry) {

			if(!array_key_exists('hint.cidr', $entry)) continue;
			if(!is_array($entry['hint.cidr'])) continue;
			
			foreach ($entry['hint.cidr'] AS $hint_entry) {
				if (SimpleSAML_Utilities::ipCIDRcheck($hint_entry, $ip)) {
					if ($type === 'entityid') {
						return $entry['entityid'];
					} else {
						return $index;
					}		
				}
			}

		}

		/* No entries matched - we should return NULL. */
		return NULL;
	}

	/*
	 *
	 */
	private function lookupIndexFromEntityId($entityId, $set) {

		assert('is_string($entityId)');
		assert('isset($set)');

		$metadataSet = $this->getMetadataSet($set);

		foreach($metadataSet AS $index => $entry)
			if ($entry['entityid'] === $entityId) return $index;
		
		return NULL;
	}

	/**
	 * This function retrieves metadata for the given entity id in the given set of metadata.
	 * It will return NULL if it is unable to locate the metadata.
	 *
	 * This class implements this function using the getMetadataSet-function. A subclass should
	 * override this function if it doesn't implement the getMetadataSet function, or if the
	 * implementation of getMetadataSet is slow.
	 *
	 * @param $index  The entityId or metaindex we are looking up.
	 * @param $set  The set we are looking for metadata in.
	 * @return An associative array with metadata for the given entity, or NULL if we are unable to
	 *         locate the entity.
	 */
	public function getMetaData($index, $set) {

		assert('is_string($index)');
		assert('isset($set)');

		$metadataSet = $this->getMetadataSet($set);

		if(array_key_exists($index, $metadataSet))
			return $metadataSet[$index];

		$indexlookup = $this->lookupIndexFromEntityId($index, $set);
		if (isset($indexlookup) && array_key_exists($indexlookup, $metadataSet)) 
			return $metadataSet[$indexlookup];

		return NULL;
	}

}
?>