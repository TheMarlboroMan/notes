class fetch_error extends Error {
	constructor(_message, _body, _status) {
		super(_message);
		this.body=_body;
		this.status_code=_status;
	}
}

//!Returns the fetch command that can be chained to others. There is no error
//!control, of course.
function chain_fetch(_url, _data) {

 	let url=_url;
	let response_type=get_fetch_val(_data, 'type');
	let fetch_data=prepare_fetch(_data, _url);
	let response_composition=fetch_data.response;

	let transform_response=(_response, _strict) => {
		switch(response_type) {
			case 'text': return _response.text();
			case 'json': return _response.json();
			case "blob": return _response.blob();
			case 'adaptative':

				if(!_response.headers.has('content-type')) {
					throw new Error("cannot use adaptative response type without content-type");
				}

				let content_type=_response.headers.get('content-type');
				if(-1!==content_type.indexOf('text/html')) {
					return _response.text();
				}

				if(-1!==content_type.indexOf('application/json')) {
					return _response.json();
				}

				if(-1!==content_type.indexOf('text/json')) {
					return _response.json();
				}

				throw new Error("could not infer adaptative type from headers '"+content_type+"'");
			default:
				if(_strict) {
					throw new Error("Unknown fetch type "+response_type);
				}
				return _response.text();
		}
	}

	switch(response_composition) {

		case 'passthrough': return fetch(url, fetch_data);
		case 'default':
			return fetch(url, fetch_data)
			.then((_response) => {

				if(!_response.ok) {

					let proc=transform_response(_response, false);
					return proc.then((_err) => {throw new fetch_error('Fetch failed', _err, _response.status);});
				}
				else {
					return _response;
				}
			})
			.then((_response) => {
				return transform_response(_response, true);
			});
		case 'status':
			return fetch(url, fetch_data)
			.then((_response) => {return _response.status;});
		case 'body':
			return fetch(url, fetch_data)
			.then((_response) => {return transform_response(_response, true);});
		case 'full':
			return fetch(url, fetch_data)
			.then((_response) => {
				let proc=transform_response(_response, false);
				return proc.then( (_proccessed) => {
					return {
						body: _proccessed,
						status_code: _response.status,
						//TODO: Perhaps the headers can be raw?
						headers: _response.headers
					};
				});
			});
	}
}

function get_fetch_val(_data, _name, _default) {
	if(undefined===_data[_name]) {

		if(undefined===_default) {
			throw new Error("Could not find "+_name+" in do_fetch data");
		}
		return _default;
	}
	return _data[_name];
}

function prepare_fetch(_data, _url) {

	//Doing fetch data...
	let fetch_data={
		method : get_fetch_val(_data, 'method'),
	};

	if(undefined===_data['ignore_credentials']) {
		fetch_data.credentials='include';
	}

	//Quickheaders and headers are mutually exclusive.
	if(undefined!==_data['quickheaders']) {

		fetch_data.headers=new Headers();

		switch(_data['quickheaders']) {
			case 'form': fetch_data.headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8'); break;
			case 'json': fetch_data.headers.append('Content-Type', 'application/json; charset=UTF-8'); break;
			case 'auto':
				//Esto es por que si se mete una cabecera content-type a fuego
				//en el fetch, y es multipart con files no te agrega el boundary
				//solo lo hace en caso de no contener ninguna cabecera
						fetch_data.headers=null;
						delete fetch_data.headers;
			break;
			default: throw new Error("Undefined quick header type: "+_data['quickheaders']);
		}
	}
	else if(undefined!==_data['headers']) {
		if(!(_data['headers'] instanceof Headers)) {
			throw new Error("do_fetch headers must be of type Headers");
		}
		else {
			fetch_data.headers=_data['headers'];
		}
	}
	else {
		fetch_data.headers=new Headers();
	}

	if(undefined!==_data['body']) {

		fetch_data.body=_data['body'];
		//Assume the worst.
		if(undefined!==fetch_data.headers && !fetch_data.headers.has('Content-Type') && fetch_data.method!='GET') {
			console.error("No Content-Type headers were given to the request, adding defaults. Fix this by adding the headers through 'headers' or 'quickheaders'. This happened when calling "+_url+" with: ", fetch_data);
			fetch_data.headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		}
	}

	if(undefined!==_data['response']) {

		switch(_data['response']) {

			case 'default':     //Lets fetch interpret the status code as possible errors, returns the body.
			case 'status':      //Returns only the status code, skips fetch checks.
			case 'body':        //Returns only the body, skips fetch checks.
			case 'full':        //Returns an object with status_code, headers and body, skips fetch checks.
			case 'passthrough': //Returns the raw fetch response.
				fetch_data.response=_data['response'];
			break;
			default:
				throw new Error("Undefined response type: "+_data['response']);
			break;
		}
	}
	else {
		fetch_data.response='default';
	}

	return fetch_data;
}

class sanitizer {

	constructor() {

		this.node=document.createElement('b');
	}

	html(_contents) {

		if(null===_contents || undefined ==_contents) {

			return "";
		}

		if(Array.isArray(_contents)) {

			return _contents.map( (_value) => {

				return this.html(_value);
			});
		}

		if(typeof _contents === "object") {

			let result={};
			Object.keys(_contents).forEach( (_item) => {

				result[_item]=this.html(_contents[_item]);
			});
			return result;
		}


		if(typeof _contents === "number") {

			return _contents;
		}

/*
		let result=String(_contents).replace(/[^\w. ]/gi, (_c) => {
			//console.debug("found ", _c, "returns", '&#'+_c.charCodeAt(0)+';');
			return '&#'+_c.charCodeAt(0)+';';
		});
		return result;
*/
		this.node.innerText=_contents;
		return this.node.innerHTML;
	}
}


function setup_form_submit(_form, _btn, _fn) {

	_form.addEventListener(
		"submit",
		(_event) => {

			_event.preventDefault();
			_fn();
			return false;
		},
		true
	);

	if(!_btn) {

		return;
	}

	_btn.addEventListener('click', () => {

		let btn=_form.querySelector('button[data-formrole="submit"]');

			if(!btn) {

				return;
			}

			btn.click();
		},
		true
	);
}

class info_bar {

	constructor(_dom) {

		this.container=_dom;
		this.timeout=null;
	}

	info(_text, _timeout) {

		this.message(_text, _timeout, "info");
	}

	warning(_text, _timeout) {

		this.message(_text, _timeout, "warning");
	}

	error(_text, _timeout) {

		this.message(_text, _timeout, "error");
	}

	message(_text, _timeout, _class) {

		//set message
		this.container.classList.remove("info", "warning", "error");
		this.container.classList.add(_class);

		this.container.innerHTML=_text;

		//start a timeout before clearing the message...
		if(null !== this.timeout) {

			clearTimeout(this.timeout);
			this.timeout=null;
		}

		if(0 !== _timeout) {

			this.timeout=setTimeout( () => {this.clear_message();}, _timeout * 1000);
		}
	}

	clear_message() {

		this.container.innerHTML="";
	}
}
