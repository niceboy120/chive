<?php

/*
 * Chive - web based MySQL database management
 * Copyright (C) 2009 Fusonic GmbH
 * 
 * This file is part of Chive.
 *
 * Chive is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * Chive is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library. If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Base controller for this project.
 *
 * Adds the following functionality:
 * * Ajax URLs (Pagination, Sorting, ...)
 * * Database connection
 */
class Controller extends CController
{

	protected $db;
	protected $request;

	/**
	 * Connects to the specified schema and assigns it to all models which need it.
	 *
	 * @param	$schema				schema
	 * @return	CDbConnection
	 */
	protected function connectDb($schema)
	{
		// Assign request
		$this->request = Yii::app()->getRequest();

		// Check parameter
		if(is_null($schema))
		{
			$this->db = null;
			return null;
		}

		// Connect to database
		$this->db = new CDbConnection('mysql:host=' . Yii::app()->user->host . ';dbname=information_schema; charset=utf8',
			utf8_decode(Yii::app()->user->name),
			utf8_decode(Yii::app()->user->password));
		$this->db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES \'utf8\'');
		$this->db->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET CHARACTER SET \'utf8\'');
		$this->db->charset='utf8';
		$this->db->active = true;
		$this->db->createCommand('USE ' . $this->db->quoteTableName($schema))->execute();

		// Assign to all models which need it
		ActiveRecord::$db =
		Routine::$db =
		Row::$db =
		Trigger::$db =
		View::$db = $this->db;

		// Return connection
		return $this->db;
	}

	/**
	 * @see		CController::filters()
	 */
	public function filters()
	{
		return array(
			'accessControl',
		);
	}

	/**
	 * @see		CController::accessRules()
	 */
	public function accessRules()
	{
		return array(
			array('deny',
				'users' => array('?'),
			),
		);
	}

	/**
	 * @see CController::createUrl()
	 */
	public function createUrl($route, $params = array(), $ampersand = '&')
	{
		if($route{0} == '#')
		{
			if(($query = CUrlManager::createPathInfo($params, '=', $ampersand)) !== '')
			{
				return $route . '?' . $query;
			}
			else
			{
				return $route;
			}
		}
		else
		{
			return parent::createUrl($route, $params, $ampersand);
		}
	}

}
