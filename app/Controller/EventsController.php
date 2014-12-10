<?php
App::uses('AppController', 'Controller');
/**
 * Events Controller
 *
 * @property Event $Event
 * @property PaginatorComponent $Paginator
 */
class EventsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'RequestHandler');

/**
 * list of events method
 *
 * @return void
 */
	public function index() {
		$this->Event->recursive = 0;
		$this->set('events', $this->Paginator->paginate());
	}
	
/**
 * view by day method
 * if the request is ajax, it returns the event grouping with date ranging from start to end dates
 * if it is requested by day, it returns events by day
 * @param string $day
 * @return void
 */
	public function view_by_day($day = null) {
		if($day) {
			$options = array('conditions' => array(
				'OR' => array(
					array(
						'YEAR(Event.event_from) = ' => date('Y', strtotime($day)), 		
						'MONTH(Event.event_from) = ' => date('m', strtotime($day)), 
						'DAY(Event.event_from) = ' => date('d', strtotime($day)),
					),
					array(
						'Event.event_from <= ' => date('Y-m-d', strtotime($day)),
						'Event.event_to >= ' => date('Y-m-d', strtotime($day))
					)
				)
			));
		}
		
		if($this->request->query) {
			$options = array('conditions' => array(
				'OR' => array(
					array(
						'Event.event_from >= ' => date('Y-m-d', strtotime($this->request->query['start'])),
						'Event.event_to >= ' => date('Y-m-d', strtotime($this->request->query['start']))
					),
					array(
						'Event.event_from <= ' => date('Y-m-d', strtotime($this->request->query['end'])),
						'Event.event_to <= ' => date('Y-m-d', strtotime($this->request->query['end']))
					)					
				)
			));			
		}
		
		$events = $this->Event->find('all', $options);
		
		if($this->RequestHandler->isAjax()) {
			$eventsByDay = array();
			foreach($events as $event) {
				for($i=0;$i<42;$i++) {
					$date = date('Y-m-d', strtotime($this->request->query['start'] . '+' . $i . ' days'));
					if(strtotime($date) >= strtotime(date('Y-m-d', strtotime($event['Event']['event_from'])))
						&& strtotime($date) <= strtotime($event['Event']['event_to'])) {
						$eventsByDay[$date][] = $event;
					}
				}
			}
			return new CakeResponse(array('body' => json_encode($eventsByDay)));			
		} else {
			$this->set('date', $day);
			$this->set('events', $events);
		}
	}	

/**
 * add method
 * add event or add event for the date
 * @param date
 * @return void
 */
	public function add($date = null) {
		if ($this->request->is('post')) {
			$this->Event->create();
			if ($event = $this->Event->save($this->request->data)) {
				$this->Session->setFlash(__('The event has been saved.'));
				return $this->redirect(array('action' => 'view_by_day', date('Y-m-d', strtotime($event['Event']['event_from']))));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.'));
			}
		}
		if($date) {
			$this->request->data['Event']['event_from'] = date('Y-m-d H:i:s', strtotime($date));
			$this->request->data['Event']['event_to'] = date('Y-m-d H:i:s', strtotime($date));
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Event->exists($id)) {
			throw new NotFoundException(__('Invalid event'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($event = $this->Event->save($this->request->data)) {
				$this->Session->setFlash(__('The event has been saved.'));
				return $this->redirect(array('action' => 'view_by_day', date('Y-m-d', strtotime($event['Event']['event_from']))));
			} else {
				$this->Session->setFlash(__('The event could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Event.' . $this->Event->primaryKey => $id));
			$this->request->data = $this->Event->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Event->id = $id;
		if (!$this->Event->exists()) {
			throw new NotFoundException(__('Invalid event'));
		}
		$event = $this->Event->read(null, $id);
		$this->request->onlyAllow('post', 'delete');
		if ($this->Event->delete()) {
			$this->Session->setFlash(__('The event has been deleted.'));
		} else {
			$this->Session->setFlash(__('The event could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'view_by_day', date('Y-m-d', strtotime($event['Event']['event_from']))));
	}
}
