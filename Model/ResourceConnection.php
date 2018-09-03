<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use FishPig\WordPress\Model\App\WPConfig;
use FishPig\WordPress\Model\Network;

class ResourceConnection
{
	/**
	 * @var 
	**/
	protected $tablePrefix = '';
	
	/**
	 * @var 
	**/
	protected $connection;
	
	/*
	 * @var Network
	 */
	protected $network;
	
	/**
	 * @var 
	**/
	protected $_tables = [];
	
	/**
	 * @var 
	**/
	public function __construct(ConnectionFactory $connectionFactory, WPConfig $wpConfig, Network $network)
	{
		$this->network  = $network;
		$this->wpConfig = $wpConfig;
		
		try {
			if ($this->connection === null) {

				$this->setTablePrefix($wpConfig->getData('DB_TABLE_PREFIX'));
				
				$this->applyMapping([
					'wordpress_menu' => 'terms',
					'wordpress_menu_item' => 'posts',
					'wordpress_post' => 'posts',
					'wordpress_post_meta' => 'postmeta',
					'wordpress_post_comment' => 'comments',
					'wordpress_post_comment_meta' => 'commentmeta',
					'wordpress_option' => 'options',
					'wordpress_term' => 'terms',
					'wordpress_term_relationship' => 'term_relationships',
					'wordpress_term_taxonomy' => 'term_taxonomy',
					'wordpress_user' => 'users',
					'wordpress_user_meta' => 'usermeta',
				]);

				$this->connection = $connectionFactory->create([
		      'host' => $wpConfig->getData('DB_HOST'),
		      'dbname' => $wpConfig->getData('DB_NAME'),
		      'username' => $wpConfig->getData('DB_USER'),
		      'password' => $wpConfig->getData('DB_PASSWORD'),
		      'active' => '1',	
				]);
			
				$this->connection->query('SET NAMES UTF8');
				
				if ($networkTables = $this->network->getNetworkTables()) {
					$this->applyMapping($networkTables);
				}
			}
		}
		catch (\Exception $e) {
			exit($e);exit;
			\FishPig\WordPress\Model\App\Integration\Exception::throwException(
				'Error connecting to the WordPress database. Check the WordPress database details in wp-config.php.',
				$e->getMessage()
			);
		}
	}

	
	/**
	 *
	 *
	 * @param string
	 * @param int $blogId = 1
	 * @return $this
	**/
	protected function applyMapping($tables)
	{
		foreach($tables as $alias => $table) {
			$this->_tables[$alias] = $this->tablePrefix . $table;
		}
		
		return $this;
	}
	
	/**
	 * Convert a table alias to a full table name
	 *
	 * @param string $alias
	 * @return string
	 **/
	public function getTable($alias)
	{
		if (($key = array_search($alias, $this->_tables)) !== false) {
			if (strpos($key, 'wordpress_') === 0) {
				return $alias;
			}
		}
		
		return isset($this->_tables[$alias])
			? $this->_tables[$alias]
			: $this->getTablePrefix() . $alias;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function isConnected()
	{
		return $this->connection !== null;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getConnection()
	{
		return $this->isConnected() ? $this->connection : false;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}
}
