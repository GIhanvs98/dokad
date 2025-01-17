<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: BeDigit | https://bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from CodeCanyon,
 * Please read the full License from here - https://codecanyon.net/licenses/standard
 */

namespace App\Http\Controllers\Web\Admin\Panel;

use App\Http\Controllers\Web\Admin\Controller;
use App\Http\Controllers\Web\Admin\Panel\Library\Panel;
use App\Http\Controllers\Web\Admin\Panel\Traits\AjaxTable;
use App\Http\Controllers\Web\Admin\Panel\Traits\BulkActions;
use App\Http\Controllers\Web\Admin\Panel\Traits\Reorder;
use App\Http\Controllers\Web\Admin\Panel\Traits\SaveActions;
use App\Http\Controllers\Web\Admin\Panel\Traits\ShowDetailsRow;
use Prologue\Alerts\Facades\Alert;

// VALIDATION
use App\Http\Requests\Admin\Request as StoreRequest;
use App\Http\Requests\Admin\Request as UpdateRequest;

class PanelController extends Controller
{
	use AjaxTable, Reorder, ShowDetailsRow, SaveActions, BulkActions;
	
	public $xPanel;
	public $data = [];
	public $request;
	
	public $parentId = 0;
	
	/**
	 * Controller constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		if (!$this->xPanel) {
			$this->xPanel = new Panel();
			$this->request = request();
			$this->xPanel->request = $this->request;
			$this->setup();
		}
	}
	
	public function setup()
	{
		// ...
	}
	
	/**
	 * Display all rows in the database for this entity.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function index()
	{
		$this->xPanel->hasAccessOrFail('list');
		
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = ucfirst($this->xPanel->entityNamePlural);
		
		// get all entries if AJAX is not enabled
		if (!$this->data['xPanel']->ajaxTable) {
			$this->data['entries'] = $this->data['xPanel']->getEntries();
		}
		
		return view('admin.panel.list', $this->data);
	}
	
	/**
	 * Show the form for creating inserting a new row.
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create()
	{
		$this->xPanel->hasAccessOrFail('create');
		
		// prepare the fields you need to show
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getCreateFields();
		$this->data['title'] = trans('admin.add') . ' ' . $this->xPanel->entityName;
		
		return view('admin.panel.create', $this->data);
	}
	
	/**
	 * Store a newly created resource in the database.
	 *
	 * @param UpdateRequest|null $request
	 * @return mixed
	 */
	public function storeCrud(StoreRequest $request = null)
	{
		$this->xPanel->hasAccessOrFail('create');
		
		// fallback to global request instance
		if (is_null($request)) {
			$request = request()->instance();
		}
		
		try {
			// replace empty values with NULL, so that it will work with MySQL strict mode on
			foreach ($request->input() as $key => $value) {
				if (empty($value) && $value !== '0') {
					$request->request->set($key, null);
				}
			}
			
			// insert item in the db
			$item = $this->xPanel->create($request->except(['redirect_after_save', '_token']));
			
			if (empty($item)) {
				Alert::error(trans('admin.error_saving_entry'))->flash();
				return back();
			}
			
			// show a success message
			Alert::success(trans('admin.insert_success'))->flash();
			
			// save the redirect choice for next time
			$this->setSaveAction();
			
			return $this->performSaveAction($item->getKey());
		} catch (\Throwable $e) {
			// Get error message
			$msg = 'Error found - [' . $e->getCode() . '] : ' . $e->getMessage() . '.';
			
			// Error notification
			Alert::error($msg)->flash();
			
			return redirect()->to($this->xPanel->route);
		}
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		// get the info for that entry
		$this->data['entry'] = (isset($entry) && !empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getUpdateFields($id);
		$this->data['title'] = trans('admin.edit') . ' ' . $this->xPanel->entityName;
		
		$this->data['id'] = $id;
		
		return view('admin.panel.edit', $this->data);
	}
	
	/**
	 * Update the specified resource in the database.
	 *
	 * @param UpdateRequest|null $request
	 * @return mixed
	 */
	public function updateCrud(UpdateRequest $request = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		// fallback to global request instance
		if (is_null($request)) {
			$request = request()->instance();
		}
		
		try {
			// replace empty values with NULL, so that it will work with MySQL strict mode on
			foreach ($request->input() as $key => $value) {
				if (empty($value) && $value !== 0 && $value !== '0') {
					$request->request->set($key, null);
				}
			}
			
			// update the row in the db
			$item = $this->xPanel->update($request->get($this->xPanel->model->getKeyName()), $request->except('redirect_after_save', '_token'));
			
			if (empty($item)) {
				Alert::error(trans('admin.error_saving_entry'))->flash();
				return back();
			}
			
			if (!$item->wasChanged()) {
				Alert::warning(t('observer_nothing_has_changed'))->flash();
				return back()->withInput();
			}
			
			// show a success message
			Alert::success(trans('admin.update_success'))->flash();
			
			// save the redirect choice for next time
			$this->setSaveAction();
			
			return $this->performSaveAction($item->getKey());
		} catch (\Throwable $e) {
			// Get error message
			$msg = 'Error found - [' . $e->getCode() . '] : ' . $e->getMessage() . '.';
			
			// Error notification
			Alert::error($msg)->flash();
			
			return redirect()->to($this->xPanel->route);
		}
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function show($id, $childId = null)
	{
		// @todo: Make the entries details by take account all possible fields
		// return redirect()->to($this->xPanel->route);
		
		$this->xPanel->hasAccessOrFail('show');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		// get the info for that entry
		$this->data['entry'] = (isset($entry) && !empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		$this->data['xPanel'] = $this->xPanel;
		$this->data['title'] = trans('admin.preview') . ' ' . $this->xPanel->entityName;
		
		return view('admin.panel.show', $this->data);
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param $id
	 * @param null $childId
	 * @return mixed
	 */
	public function destroy($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('delete');
		
		if (!empty($childId)) {
			$id = $childId;
		}
		
		return $this->xPanel->delete($id);
	}
}
