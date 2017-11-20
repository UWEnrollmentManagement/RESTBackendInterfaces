<?php

namespace FormsAPI\Base;

use \Exception;
use \PDO;
use FormsAPI\ChildFormRelationship as ChildChildFormRelationship;
use FormsAPI\ChildFormRelationshipQuery as ChildChildFormRelationshipQuery;
use FormsAPI\FormReaction as ChildFormReaction;
use FormsAPI\FormReactionQuery as ChildFormReactionQuery;
use FormsAPI\Reaction as ChildReaction;
use FormsAPI\ReactionQuery as ChildReactionQuery;
use FormsAPI\Map\ChildFormRelationshipTableMap;
use FormsAPI\Map\FormReactionTableMap;
use FormsAPI\Map\ReactionTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Base class that represents a row from the 'reaction' table.
 *
 *
 *
 * @package    propel.generator.FormsAPI.Base
 */
abstract class Reaction implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\FormsAPI\\Map\\ReactionTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the subject field.
     *
     * @var        string
     */
    protected $subject;

    /**
     * The value for the recipient field.
     *
     * @var        string
     */
    protected $recipient;

    /**
     * The value for the sender field.
     *
     * @var        string
     */
    protected $sender;

    /**
     * The value for the reply_to field.
     *
     * @var        string
     */
    protected $reply_to;

    /**
     * The value for the cc field.
     *
     * @var        string
     */
    protected $cc;

    /**
     * The value for the bcc field.
     *
     * @var        string
     */
    protected $bcc;

    /**
     * The value for the template field.
     *
     * @var        string
     */
    protected $template;

    /**
     * The value for the content field.
     *
     * @var        string
     */
    protected $content;

    /**
     * @var        ObjectCollection|ChildChildFormRelationship[] Collection to store aggregation of ChildChildFormRelationship objects.
     */
    protected $collChildFormRelationships;
    protected $collChildFormRelationshipsPartial;

    /**
     * @var        ObjectCollection|ChildFormReaction[] Collection to store aggregation of ChildFormReaction objects.
     */
    protected $collFormReactions;
    protected $collFormReactionsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // validate behavior

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * ConstraintViolationList object
     *
     * @see     http://api.symfony.com/2.0/Symfony/Component/Validator/ConstraintViolationList.html
     * @var     ConstraintViolationList
     */
    protected $validationFailures;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildChildFormRelationship[]
     */
    protected $childFormRelationshipsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildFormReaction[]
     */
    protected $formReactionsScheduledForDeletion = null;

    /**
     * Initializes internal state of FormsAPI\Base\Reaction object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Reaction</code> instance.  If
     * <code>obj</code> is an instance of <code>Reaction</code>, delegates to
     * <code>equals(Reaction)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|Reaction The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [subject] column value.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get the [recipient] column value.
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Get the [sender] column value.
     *
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Get the [reply_to] column value.
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * Get the [cc] column value.
     *
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Get the [bcc] column value.
     *
     * @return string
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Get the [template] column value.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get the [content] column value.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ReactionTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [subject] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setSubject($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->subject !== $v) {
            $this->subject = $v;
            $this->modifiedColumns[ReactionTableMap::COL_SUBJECT] = true;
        }

        return $this;
    } // setSubject()

    /**
     * Set the value of [recipient] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setRecipient($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->recipient !== $v) {
            $this->recipient = $v;
            $this->modifiedColumns[ReactionTableMap::COL_RECIPIENT] = true;
        }

        return $this;
    } // setRecipient()

    /**
     * Set the value of [sender] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setSender($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->sender !== $v) {
            $this->sender = $v;
            $this->modifiedColumns[ReactionTableMap::COL_SENDER] = true;
        }

        return $this;
    } // setSender()

    /**
     * Set the value of [reply_to] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setReplyTo($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->reply_to !== $v) {
            $this->reply_to = $v;
            $this->modifiedColumns[ReactionTableMap::COL_REPLY_TO] = true;
        }

        return $this;
    } // setReplyTo()

    /**
     * Set the value of [cc] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setCc($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->cc !== $v) {
            $this->cc = $v;
            $this->modifiedColumns[ReactionTableMap::COL_CC] = true;
        }

        return $this;
    } // setCc()

    /**
     * Set the value of [bcc] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setBcc($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->bcc !== $v) {
            $this->bcc = $v;
            $this->modifiedColumns[ReactionTableMap::COL_BCC] = true;
        }

        return $this;
    } // setBcc()

    /**
     * Set the value of [template] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setTemplate($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->template !== $v) {
            $this->template = $v;
            $this->modifiedColumns[ReactionTableMap::COL_TEMPLATE] = true;
        }

        return $this;
    } // setTemplate()

    /**
     * Set the value of [content] column.
     *
     * @param string $v new value
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function setContent($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->content !== $v) {
            $this->content = $v;
            $this->modifiedColumns[ReactionTableMap::COL_CONTENT] = true;
        }

        return $this;
    } // setContent()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ReactionTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ReactionTableMap::translateFieldName('Subject', TableMap::TYPE_PHPNAME, $indexType)];
            $this->subject = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ReactionTableMap::translateFieldName('Recipient', TableMap::TYPE_PHPNAME, $indexType)];
            $this->recipient = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ReactionTableMap::translateFieldName('Sender', TableMap::TYPE_PHPNAME, $indexType)];
            $this->sender = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ReactionTableMap::translateFieldName('ReplyTo', TableMap::TYPE_PHPNAME, $indexType)];
            $this->reply_to = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ReactionTableMap::translateFieldName('Cc', TableMap::TYPE_PHPNAME, $indexType)];
            $this->cc = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : ReactionTableMap::translateFieldName('Bcc', TableMap::TYPE_PHPNAME, $indexType)];
            $this->bcc = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : ReactionTableMap::translateFieldName('Template', TableMap::TYPE_PHPNAME, $indexType)];
            $this->template = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : ReactionTableMap::translateFieldName('Content', TableMap::TYPE_PHPNAME, $indexType)];
            $this->content = (null !== $col) ? (string) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 9; // 9 = ReactionTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\FormsAPI\\Reaction'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ReactionTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildReactionQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collChildFormRelationships = null;

            $this->collFormReactions = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Reaction::setDeleted()
     * @see Reaction::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ReactionTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildReactionQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ReactionTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ReactionTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->childFormRelationshipsScheduledForDeletion !== null) {
                if (!$this->childFormRelationshipsScheduledForDeletion->isEmpty()) {
                    foreach ($this->childFormRelationshipsScheduledForDeletion as $childFormRelationship) {
                        // need to save related object because we set the relation to null
                        $childFormRelationship->save($con);
                    }
                    $this->childFormRelationshipsScheduledForDeletion = null;
                }
            }

            if ($this->collChildFormRelationships !== null) {
                foreach ($this->collChildFormRelationships as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->formReactionsScheduledForDeletion !== null) {
                if (!$this->formReactionsScheduledForDeletion->isEmpty()) {
                    \FormsAPI\FormReactionQuery::create()
                        ->filterByPrimaryKeys($this->formReactionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->formReactionsScheduledForDeletion = null;
                }
            }

            if ($this->collFormReactions !== null) {
                foreach ($this->collFormReactions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[ReactionTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ReactionTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ReactionTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_SUBJECT)) {
            $modifiedColumns[':p' . $index++]  = 'subject';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_RECIPIENT)) {
            $modifiedColumns[':p' . $index++]  = 'recipient';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_SENDER)) {
            $modifiedColumns[':p' . $index++]  = 'sender';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_REPLY_TO)) {
            $modifiedColumns[':p' . $index++]  = 'reply_to';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_CC)) {
            $modifiedColumns[':p' . $index++]  = 'cc';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_BCC)) {
            $modifiedColumns[':p' . $index++]  = 'bcc';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_TEMPLATE)) {
            $modifiedColumns[':p' . $index++]  = 'template';
        }
        if ($this->isColumnModified(ReactionTableMap::COL_CONTENT)) {
            $modifiedColumns[':p' . $index++]  = 'content';
        }

        $sql = sprintf(
            'INSERT INTO reaction (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'subject':
                        $stmt->bindValue($identifier, $this->subject, PDO::PARAM_STR);
                        break;
                    case 'recipient':
                        $stmt->bindValue($identifier, $this->recipient, PDO::PARAM_STR);
                        break;
                    case 'sender':
                        $stmt->bindValue($identifier, $this->sender, PDO::PARAM_STR);
                        break;
                    case 'reply_to':
                        $stmt->bindValue($identifier, $this->reply_to, PDO::PARAM_STR);
                        break;
                    case 'cc':
                        $stmt->bindValue($identifier, $this->cc, PDO::PARAM_STR);
                        break;
                    case 'bcc':
                        $stmt->bindValue($identifier, $this->bcc, PDO::PARAM_STR);
                        break;
                    case 'template':
                        $stmt->bindValue($identifier, $this->template, PDO::PARAM_STR);
                        break;
                    case 'content':
                        $stmt->bindValue($identifier, $this->content, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ReactionTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getSubject();
                break;
            case 2:
                return $this->getRecipient();
                break;
            case 3:
                return $this->getSender();
                break;
            case 4:
                return $this->getReplyTo();
                break;
            case 5:
                return $this->getCc();
                break;
            case 6:
                return $this->getBcc();
                break;
            case 7:
                return $this->getTemplate();
                break;
            case 8:
                return $this->getContent();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['Reaction'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Reaction'][$this->hashCode()] = true;
        $keys = ReactionTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getSubject(),
            $keys[2] => $this->getRecipient(),
            $keys[3] => $this->getSender(),
            $keys[4] => $this->getReplyTo(),
            $keys[5] => $this->getCc(),
            $keys[6] => $this->getBcc(),
            $keys[7] => $this->getTemplate(),
            $keys[8] => $this->getContent(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collChildFormRelationships) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'childFormRelationships';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'child_form_relationships';
                        break;
                    default:
                        $key = 'ChildFormRelationships';
                }

                $result[$key] = $this->collChildFormRelationships->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collFormReactions) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'formReactions';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'form_reactions';
                        break;
                    default:
                        $key = 'FormReactions';
                }

                $result[$key] = $this->collFormReactions->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\FormsAPI\Reaction
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ReactionTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\FormsAPI\Reaction
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setSubject($value);
                break;
            case 2:
                $this->setRecipient($value);
                break;
            case 3:
                $this->setSender($value);
                break;
            case 4:
                $this->setReplyTo($value);
                break;
            case 5:
                $this->setCc($value);
                break;
            case 6:
                $this->setBcc($value);
                break;
            case 7:
                $this->setTemplate($value);
                break;
            case 8:
                $this->setContent($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = ReactionTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setSubject($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setRecipient($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSender($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setReplyTo($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setCc($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setBcc($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setTemplate($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setContent($arr[$keys[8]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\FormsAPI\Reaction The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ReactionTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ReactionTableMap::COL_ID)) {
            $criteria->add(ReactionTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_SUBJECT)) {
            $criteria->add(ReactionTableMap::COL_SUBJECT, $this->subject);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_RECIPIENT)) {
            $criteria->add(ReactionTableMap::COL_RECIPIENT, $this->recipient);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_SENDER)) {
            $criteria->add(ReactionTableMap::COL_SENDER, $this->sender);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_REPLY_TO)) {
            $criteria->add(ReactionTableMap::COL_REPLY_TO, $this->reply_to);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_CC)) {
            $criteria->add(ReactionTableMap::COL_CC, $this->cc);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_BCC)) {
            $criteria->add(ReactionTableMap::COL_BCC, $this->bcc);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_TEMPLATE)) {
            $criteria->add(ReactionTableMap::COL_TEMPLATE, $this->template);
        }
        if ($this->isColumnModified(ReactionTableMap::COL_CONTENT)) {
            $criteria->add(ReactionTableMap::COL_CONTENT, $this->content);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildReactionQuery::create();
        $criteria->add(ReactionTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \FormsAPI\Reaction (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSubject($this->getSubject());
        $copyObj->setRecipient($this->getRecipient());
        $copyObj->setSender($this->getSender());
        $copyObj->setReplyTo($this->getReplyTo());
        $copyObj->setCc($this->getCc());
        $copyObj->setBcc($this->getBcc());
        $copyObj->setTemplate($this->getTemplate());
        $copyObj->setContent($this->getContent());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getChildFormRelationships() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addChildFormRelationship($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getFormReactions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFormReaction($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \FormsAPI\Reaction Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('ChildFormRelationship' == $relationName) {
            $this->initChildFormRelationships();
            return;
        }
        if ('FormReaction' == $relationName) {
            $this->initFormReactions();
            return;
        }
    }

    /**
     * Clears out the collChildFormRelationships collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addChildFormRelationships()
     */
    public function clearChildFormRelationships()
    {
        $this->collChildFormRelationships = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collChildFormRelationships collection loaded partially.
     */
    public function resetPartialChildFormRelationships($v = true)
    {
        $this->collChildFormRelationshipsPartial = $v;
    }

    /**
     * Initializes the collChildFormRelationships collection.
     *
     * By default this just sets the collChildFormRelationships collection to an empty array (like clearcollChildFormRelationships());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initChildFormRelationships($overrideExisting = true)
    {
        if (null !== $this->collChildFormRelationships && !$overrideExisting) {
            return;
        }

        $collectionClassName = ChildFormRelationshipTableMap::getTableMap()->getCollectionClassName();

        $this->collChildFormRelationships = new $collectionClassName;
        $this->collChildFormRelationships->setModel('\FormsAPI\ChildFormRelationship');
    }

    /**
     * Gets an array of ChildChildFormRelationship objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildReaction is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildChildFormRelationship[] List of ChildChildFormRelationship objects
     * @throws PropelException
     */
    public function getChildFormRelationships(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collChildFormRelationshipsPartial && !$this->isNew();
        if (null === $this->collChildFormRelationships || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collChildFormRelationships) {
                // return empty collection
                $this->initChildFormRelationships();
            } else {
                $collChildFormRelationships = ChildChildFormRelationshipQuery::create(null, $criteria)
                    ->filterByReaction($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collChildFormRelationshipsPartial && count($collChildFormRelationships)) {
                        $this->initChildFormRelationships(false);

                        foreach ($collChildFormRelationships as $obj) {
                            if (false == $this->collChildFormRelationships->contains($obj)) {
                                $this->collChildFormRelationships->append($obj);
                            }
                        }

                        $this->collChildFormRelationshipsPartial = true;
                    }

                    return $collChildFormRelationships;
                }

                if ($partial && $this->collChildFormRelationships) {
                    foreach ($this->collChildFormRelationships as $obj) {
                        if ($obj->isNew()) {
                            $collChildFormRelationships[] = $obj;
                        }
                    }
                }

                $this->collChildFormRelationships = $collChildFormRelationships;
                $this->collChildFormRelationshipsPartial = false;
            }
        }

        return $this->collChildFormRelationships;
    }

    /**
     * Sets a collection of ChildChildFormRelationship objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $childFormRelationships A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildReaction The current object (for fluent API support)
     */
    public function setChildFormRelationships(Collection $childFormRelationships, ConnectionInterface $con = null)
    {
        /** @var ChildChildFormRelationship[] $childFormRelationshipsToDelete */
        $childFormRelationshipsToDelete = $this->getChildFormRelationships(new Criteria(), $con)->diff($childFormRelationships);


        $this->childFormRelationshipsScheduledForDeletion = $childFormRelationshipsToDelete;

        foreach ($childFormRelationshipsToDelete as $childFormRelationshipRemoved) {
            $childFormRelationshipRemoved->setReaction(null);
        }

        $this->collChildFormRelationships = null;
        foreach ($childFormRelationships as $childFormRelationship) {
            $this->addChildFormRelationship($childFormRelationship);
        }

        $this->collChildFormRelationships = $childFormRelationships;
        $this->collChildFormRelationshipsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ChildFormRelationship objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related ChildFormRelationship objects.
     * @throws PropelException
     */
    public function countChildFormRelationships(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collChildFormRelationshipsPartial && !$this->isNew();
        if (null === $this->collChildFormRelationships || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collChildFormRelationships) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getChildFormRelationships());
            }

            $query = ChildChildFormRelationshipQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByReaction($this)
                ->count($con);
        }

        return count($this->collChildFormRelationships);
    }

    /**
     * Method called to associate a ChildChildFormRelationship object to this object
     * through the ChildChildFormRelationship foreign key attribute.
     *
     * @param  ChildChildFormRelationship $l ChildChildFormRelationship
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function addChildFormRelationship(ChildChildFormRelationship $l)
    {
        if ($this->collChildFormRelationships === null) {
            $this->initChildFormRelationships();
            $this->collChildFormRelationshipsPartial = true;
        }

        if (!$this->collChildFormRelationships->contains($l)) {
            $this->doAddChildFormRelationship($l);

            if ($this->childFormRelationshipsScheduledForDeletion and $this->childFormRelationshipsScheduledForDeletion->contains($l)) {
                $this->childFormRelationshipsScheduledForDeletion->remove($this->childFormRelationshipsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildChildFormRelationship $childFormRelationship The ChildChildFormRelationship object to add.
     */
    protected function doAddChildFormRelationship(ChildChildFormRelationship $childFormRelationship)
    {
        $this->collChildFormRelationships[]= $childFormRelationship;
        $childFormRelationship->setReaction($this);
    }

    /**
     * @param  ChildChildFormRelationship $childFormRelationship The ChildChildFormRelationship object to remove.
     * @return $this|ChildReaction The current object (for fluent API support)
     */
    public function removeChildFormRelationship(ChildChildFormRelationship $childFormRelationship)
    {
        if ($this->getChildFormRelationships()->contains($childFormRelationship)) {
            $pos = $this->collChildFormRelationships->search($childFormRelationship);
            $this->collChildFormRelationships->remove($pos);
            if (null === $this->childFormRelationshipsScheduledForDeletion) {
                $this->childFormRelationshipsScheduledForDeletion = clone $this->collChildFormRelationships;
                $this->childFormRelationshipsScheduledForDeletion->clear();
            }
            $this->childFormRelationshipsScheduledForDeletion[]= $childFormRelationship;
            $childFormRelationship->setReaction(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Reaction is new, it will return
     * an empty collection; or if this Reaction has previously
     * been saved, it will retrieve related ChildFormRelationships from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Reaction.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildChildFormRelationship[] List of ChildChildFormRelationship objects
     */
    public function getChildFormRelationshipsJoinParent(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildChildFormRelationshipQuery::create(null, $criteria);
        $query->joinWith('Parent', $joinBehavior);

        return $this->getChildFormRelationships($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Reaction is new, it will return
     * an empty collection; or if this Reaction has previously
     * been saved, it will retrieve related ChildFormRelationships from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Reaction.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildChildFormRelationship[] List of ChildChildFormRelationship objects
     */
    public function getChildFormRelationshipsJoinChild(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildChildFormRelationshipQuery::create(null, $criteria);
        $query->joinWith('Child', $joinBehavior);

        return $this->getChildFormRelationships($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Reaction is new, it will return
     * an empty collection; or if this Reaction has previously
     * been saved, it will retrieve related ChildFormRelationships from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Reaction.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildChildFormRelationship[] List of ChildChildFormRelationship objects
     */
    public function getChildFormRelationshipsJoinTag(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildChildFormRelationshipQuery::create(null, $criteria);
        $query->joinWith('Tag', $joinBehavior);

        return $this->getChildFormRelationships($query, $con);
    }

    /**
     * Clears out the collFormReactions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addFormReactions()
     */
    public function clearFormReactions()
    {
        $this->collFormReactions = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collFormReactions collection loaded partially.
     */
    public function resetPartialFormReactions($v = true)
    {
        $this->collFormReactionsPartial = $v;
    }

    /**
     * Initializes the collFormReactions collection.
     *
     * By default this just sets the collFormReactions collection to an empty array (like clearcollFormReactions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFormReactions($overrideExisting = true)
    {
        if (null !== $this->collFormReactions && !$overrideExisting) {
            return;
        }

        $collectionClassName = FormReactionTableMap::getTableMap()->getCollectionClassName();

        $this->collFormReactions = new $collectionClassName;
        $this->collFormReactions->setModel('\FormsAPI\FormReaction');
    }

    /**
     * Gets an array of ChildFormReaction objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildReaction is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildFormReaction[] List of ChildFormReaction objects
     * @throws PropelException
     */
    public function getFormReactions(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collFormReactionsPartial && !$this->isNew();
        if (null === $this->collFormReactions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collFormReactions) {
                // return empty collection
                $this->initFormReactions();
            } else {
                $collFormReactions = ChildFormReactionQuery::create(null, $criteria)
                    ->filterByReaction($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collFormReactionsPartial && count($collFormReactions)) {
                        $this->initFormReactions(false);

                        foreach ($collFormReactions as $obj) {
                            if (false == $this->collFormReactions->contains($obj)) {
                                $this->collFormReactions->append($obj);
                            }
                        }

                        $this->collFormReactionsPartial = true;
                    }

                    return $collFormReactions;
                }

                if ($partial && $this->collFormReactions) {
                    foreach ($this->collFormReactions as $obj) {
                        if ($obj->isNew()) {
                            $collFormReactions[] = $obj;
                        }
                    }
                }

                $this->collFormReactions = $collFormReactions;
                $this->collFormReactionsPartial = false;
            }
        }

        return $this->collFormReactions;
    }

    /**
     * Sets a collection of ChildFormReaction objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $formReactions A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildReaction The current object (for fluent API support)
     */
    public function setFormReactions(Collection $formReactions, ConnectionInterface $con = null)
    {
        /** @var ChildFormReaction[] $formReactionsToDelete */
        $formReactionsToDelete = $this->getFormReactions(new Criteria(), $con)->diff($formReactions);


        $this->formReactionsScheduledForDeletion = $formReactionsToDelete;

        foreach ($formReactionsToDelete as $formReactionRemoved) {
            $formReactionRemoved->setReaction(null);
        }

        $this->collFormReactions = null;
        foreach ($formReactions as $formReaction) {
            $this->addFormReaction($formReaction);
        }

        $this->collFormReactions = $formReactions;
        $this->collFormReactionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related FormReaction objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related FormReaction objects.
     * @throws PropelException
     */
    public function countFormReactions(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collFormReactionsPartial && !$this->isNew();
        if (null === $this->collFormReactions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFormReactions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFormReactions());
            }

            $query = ChildFormReactionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByReaction($this)
                ->count($con);
        }

        return count($this->collFormReactions);
    }

    /**
     * Method called to associate a ChildFormReaction object to this object
     * through the ChildFormReaction foreign key attribute.
     *
     * @param  ChildFormReaction $l ChildFormReaction
     * @return $this|\FormsAPI\Reaction The current object (for fluent API support)
     */
    public function addFormReaction(ChildFormReaction $l)
    {
        if ($this->collFormReactions === null) {
            $this->initFormReactions();
            $this->collFormReactionsPartial = true;
        }

        if (!$this->collFormReactions->contains($l)) {
            $this->doAddFormReaction($l);

            if ($this->formReactionsScheduledForDeletion and $this->formReactionsScheduledForDeletion->contains($l)) {
                $this->formReactionsScheduledForDeletion->remove($this->formReactionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildFormReaction $formReaction The ChildFormReaction object to add.
     */
    protected function doAddFormReaction(ChildFormReaction $formReaction)
    {
        $this->collFormReactions[]= $formReaction;
        $formReaction->setReaction($this);
    }

    /**
     * @param  ChildFormReaction $formReaction The ChildFormReaction object to remove.
     * @return $this|ChildReaction The current object (for fluent API support)
     */
    public function removeFormReaction(ChildFormReaction $formReaction)
    {
        if ($this->getFormReactions()->contains($formReaction)) {
            $pos = $this->collFormReactions->search($formReaction);
            $this->collFormReactions->remove($pos);
            if (null === $this->formReactionsScheduledForDeletion) {
                $this->formReactionsScheduledForDeletion = clone $this->collFormReactions;
                $this->formReactionsScheduledForDeletion->clear();
            }
            $this->formReactionsScheduledForDeletion[]= clone $formReaction;
            $formReaction->setReaction(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Reaction is new, it will return
     * an empty collection; or if this Reaction has previously
     * been saved, it will retrieve related FormReactions from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Reaction.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildFormReaction[] List of ChildFormReaction objects
     */
    public function getFormReactionsJoinForm(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildFormReactionQuery::create(null, $criteria);
        $query->joinWith('Form', $joinBehavior);

        return $this->getFormReactions($query, $con);
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->id = null;
        $this->subject = null;
        $this->recipient = null;
        $this->sender = null;
        $this->reply_to = null;
        $this->cc = null;
        $this->bcc = null;
        $this->template = null;
        $this->content = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collChildFormRelationships) {
                foreach ($this->collChildFormRelationships as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collFormReactions) {
                foreach ($this->collFormReactions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collChildFormRelationships = null;
        $this->collFormReactions = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ReactionTableMap::DEFAULT_STRING_FORMAT);
    }

    // validate behavior

    /**
     * Configure validators constraints. The Validator object uses this method
     * to perform object validation.
     *
     * @param ClassMetadata $metadata
     */
    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('subject', new NotNull());
        $metadata->addPropertyConstraint('recipient', new NotNull());
        $metadata->addPropertyConstraint('sender', new NotNull());
        $metadata->addPropertyConstraint('template', new NotNull());
        $metadata->addPropertyConstraint('content', new NotNull());
    }

    /**
     * Validates the object and all objects related to this table.
     *
     * @see        getValidationFailures()
     * @param      ValidatorInterface|null $validator A Validator class instance
     * @return     boolean Whether all objects pass validation.
     */
    public function validate(ValidatorInterface $validator = null)
    {
        if (null === $validator) {
            $validator = new RecursiveValidator(
                new ExecutionContextFactory(new IdentityTranslator()),
                new LazyLoadingMetadataFactory(new StaticMethodLoader()),
                new ConstraintValidatorFactory()
            );
        }

        $failureMap = new ConstraintViolationList();

        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;


            $retval = $validator->validate($this);
            if (count($retval) > 0) {
                $failureMap->addAll($retval);
            }

            if (null !== $this->collChildFormRelationships) {
                foreach ($this->collChildFormRelationships as $referrerFK) {
                    if (method_exists($referrerFK, 'validate')) {
                        if (!$referrerFK->validate($validator)) {
                            $failureMap->addAll($referrerFK->getValidationFailures());
                        }
                    }
                }
            }
            if (null !== $this->collFormReactions) {
                foreach ($this->collFormReactions as $referrerFK) {
                    if (method_exists($referrerFK, 'validate')) {
                        if (!$referrerFK->validate($validator)) {
                            $failureMap->addAll($referrerFK->getValidationFailures());
                        }
                    }
                }
            }

            $this->alreadyInValidation = false;
        }

        $this->validationFailures = $failureMap;

        return (Boolean) (!(count($this->validationFailures) > 0));

    }

    /**
     * Gets any ConstraintViolation objects that resulted from last call to validate().
     *
     *
     * @return     object ConstraintViolationList
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}