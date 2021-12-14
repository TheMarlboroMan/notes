class api {

	constructor(_token) {

		this.root=document.baseURI+"api/";
		this.token=_token;
	}

	post(_endpoint, _payload, _expected) {

		return this.with_body("POST", _endpoint, _payload, _expected);
	}

	patch(_endpoint, _payload, _expected) {

		return this.with_body("PATCH", _endpoint, _payload, _expected);
	}

	delete(_endpoint, _payload, _expected) {

		return this.with_body("DELETE", _endpoint, _payload, _expected);
	}

	with_body(_method, _endpoint, _payload, _expected) {

		return chain_fetch(
			this.root+_endpoint,
			{method:_method, response:"full", type:"json", headers:this.build_headers(_method), body:JSON.stringify(_payload)}
		)
		.then( (_res) => {

			if(-1===_expected.indexOf(_res.status_code)) {

				throw new Error("invalid status code "+_res.status_code);
			}

			return _res;
		});
	}

	get(_endpoint, _expected) {

		let method="GET";

		return chain_fetch(
			this.root+_endpoint,
			{method:method, response:"full", type:"json", headers:this.build_headers(method)}
		)
		.then( (_res) => {

			if(-1===_expected.indexOf(_res.status_code)) {

				throw new Error("invalid status code "+_res.status_code);
			}

			return _res;
		});
	}

	build_headers(_method) {

		let headers=new Headers();

		if(_method!=="GET") {

			headers.append("content-type", "application/json; charset=UTF-8");
		}

		headers.append("notes-auth_token", this.token);
		return headers;
	}
}

const status_regular=1;
const status_locked=2;
const status_settings=3;
const status_help=4;

class toolbar {

	constructor(_dom, _note_builder, _api, _storage) {

		this.note_builder=_note_builder;
		this.api=_api;
		this.sanitizer_i=new sanitizer();
		this.storage=_storage;

		this.status=status_regular;
		this.nextstatus=0;

		this.dom_root=_dom;
		this.tools_container=_dom.querySelector("[data-role='tools_container']");

		this.username_container=_dom.querySelector("#username_container");

		this.button_container=_dom.querySelector("[data-role='button_container']");
		this.btn_new=_dom.querySelector("button[name='btn_new']");
		this.btn_settings=_dom.querySelector("button[name='btn_settings']");
		this.btn_logout=_dom.querySelector("button[name='btn_logout']");
		this.btn_help=_dom.querySelector("button[name='btn_help']");

		this.update_user_container=_dom.querySelector("[data-role='user_update']");
		this.btn_close_update_user=this.update_user_container.querySelector("button[name='btn_close']");
		this.btn_send_update_user=this.update_user_container.querySelector("button[name='btn_send']");
		this.input_username=this.update_user_container.querySelector("input[name='username']");
		this.input_password=this.update_user_container.querySelector("input[name='password']");
		this.input_repeat_password=this.update_user_container.querySelector("input[name='repeat_password']");

		this.help_container=_dom.querySelector("[data-role='help']");
		this.btn_close_help=this.help_container.querySelector("button[name='btn_close']");

		this.api.get("me", [200])
		.then( (_res) => {

			this.set_username(_res.body.username);
		})
		.then( (_res) => {

			this.btn_new.addEventListener("click", () => {this.new_note();}, true);
			this.btn_logout.addEventListener("click", () => {this.logout();}, true);
			this.btn_send_update_user.addEventListener("click", () => {this.update_user("help");}, true);

			this.btn_settings.addEventListener("click", () => {this.toggle_mode(status_settings, "+settings");}, true);
			this.btn_help.addEventListener("click", () => {this.toggle_mode(status_help, "+help");}, true);
			this.btn_close_update_user.addEventListener("click", () => {this.toggle_mode(status_regular, "-settings");}, true);
			this.btn_close_help.addEventListener("click", () => {this.toggle_mode(status_regular, "-help");}, true);

			let end_transition=() => {
				this.status=this.nextstatus;
				this.nextstatus=0;
			};

			this.update_user_container.addEventListener("transitionend", end_transition, true);
			this.help_container.addEventListener("transitionend", end_transition, true);
		})
		.catch( (_err) => {

			console.error(_err);
			alert(_err);
		});
	}

	show() {

		this.dom_root.style.display="grid";
	}

	hide() {

		this.dom_root.style.display="none";
	}

	set_username(_username) {

		this.username_container.innerHTML=this.sanitizer_i.html(_username);
	}

	new_note() {

		if(status_regular !== this.status) {

			return;
		}

		let note=this.note_builder.build(true);
		note.setup_as_new();
	}

	toggle_mode(_new_status, _classname) {

		if(status_locked===this.status) {

			return;
		}

		this.status=status_locked;
		this.nextstatus=_new_status;

		let classname=_classname.substring(1);
		switch(_classname.substring(0, 1)) {

			case "+": this.tools_container.classList.add(classname); break;
			case "-": this.tools_container.classList.remove(classname); break;
		}
	}

	update_user() {

		if(status_settings !== this.status) {

			return;
		}

		let username=this.input_username.value.trim();
		let password=this.input_password.value.trim();
		let repeat_password=this.input_repeat_password.value.trim();

		let payload={};

		if(!username.length && !password.length) {

			alert("username or password must be specified");
			return;
		}

		if(username.length) {

			payload.username=username;
		}

		if(password.length) {

			if(password != repeat_password) {

				alert("passwords must be the same");
				return;
			}

			payload.pass=password;
		}

		this.status=status_locked;

		this.api.patch("me", payload, [200, 400])
		.then( (_res) => {

			if(200===_res.status_code) {

				this.set_username(username);
				alert("your changes have been saved correctly");
				return;
			}

			alert(_res.body);

		})
		.catch( (_err) => {

			console.error(_err);
			alert(_err);

		})
		.finally( () => {

			this.status=status_settings;
		});
	}

	logout() {

		if(status_regular !== this.status) {

			return;
		}

		this.api.post("logout", {}, [200])
		.then( (_res) => {

			this.storage.removeItem("auth-token");
			window.location.reload();
		})
		.catch( (_err) => {

			console.error(_err);
			alert(_err);
		});
	}
}

class note_builder {

	constructor(_note_template, _workspace, _api) {

		this.note_template=_note_template;
		this.workspace=_workspace;
		this.api=_api;
	}

	build(_first) {

		let clone=document.importNode(this.note_template.content, true);

		if(_first) {

			this.workspace.insertBefore(clone, this.workspace.querySelector(".note:first-child"));
			let dom_note=this.workspace.querySelector(".note:first-child");
			return new note(dom_note, this.api);
		}
		else {

			this.workspace.appendChild(clone);
			let dom_note=this.workspace.querySelector(".note:last-child");
			return new note(dom_note, this.api);
		}
	}
}

class note {

	constructor(_dom, _api) {

		this.editing=false;

		this.created_at=null;
		this.last_updated_at=null;
		this.id=0;
		this.color_id=1;
		this.text="";

		this.dom_root=_dom;
		this.api=_api;

		this.textarea=this.dom_root.querySelector("textarea");
		this.text_container=this.dom_root.querySelector(".body .text");
		this.created_at_container=this.dom_root.querySelector("dd[data-contents='created_at']");
		this.last_updated_at_container=this.dom_root.querySelector("dd[data-contents='last_updated_at']");

		this.btn_delete=this.dom_root.querySelector("button[name='btn_delete']");
		this.btn_color=this.dom_root.querySelector("button[name='btn_color']");
		this.btn_edit=this.dom_root.querySelector("button[name='btn_edit']");

		this.ev_btn_delete=this.btn_delete.addEventListener("click", () => {this.delete();}, true);
		this.ev_btn_color=this.btn_color.addEventListener("click", () => {this.cycle_color();}, true);
		this.ev_btn_edit=this.btn_edit.addEventListener("click", () => {this.toggle_edit();}, true);
		this.ev_text_container=this.text_container.addEventListener("click", () => {this.toggle_edit();}, true);
		this.text=this.textarea.value.trim();
	}

	unload_events() {

		this.btn_delete.removeEventListener("click", this.ev_btn_delete);
		this.btn_color.removeEventListener("click", this.ev_btn_color);
		this.btn_edit.removeEventListener("click", this.ev_btn_edit);
		this.text_container.removeEventListener("click", this.ev_text_container);
		this.ev_btn_delete=null;
		this.ev_btn_color=null;
		this.ev_btn_edit=null;
		this.ev_text_container=null;
	}

	load_from_node(_node) {

		this.id=_node.id;
		this.color_id=_node.color_id;
		this.created_at=new Date(_node.created_at.date);
		this.last_updated_at=null===_node.last_updated_at
			? null
			: new Date(_node.last_updated_at.date);
		this.text=_node.contents;

		this.textarea.value=this.text;
		this.set_dom_color(this.color_id);
		this.text_container.innerHTML=this.text_to_view(this.text);
		this.created_at_container.innerHTML=this.format_date(this.created_at);
		this.last_updated_at_container.innerHTML=null===this.last_updated_at
			? "never"
			: this.format_date(this.last_updated_at);
	}

	format_date(_date) {

		let lpad=(_value, _len, _pad) => {

			let value=""+_value;
			if(value.length < _len) {

				return _pad.repeat(_len-value.length)+value;
			}

			return value;

		};

		return _date.getDate()+"-"+(_date.getMonth()+1)+"-"+_date.getFullYear()
			+" "
			+lpad(_date.getHours(), 2, "0")+":"+lpad(_date.getMinutes(), 2, "0");
	}

	setup_as_new() {

		this.dom_root.classList.add("new", "read");
	}

	setup_as_loaded() {

		this.dom_root.classList.add("read");
	}

	toggle_edit() {

		this.editing
			? this.set_as_read()
			: this.set_as_editing();

		this.editing=!this.editing;
	}

	set_as_read() {

		let text=this.textarea.value.trim();

		this.dom_root.classList.remove("edit");
		this.dom_root.classList.add("read");

		//save if there were changes.
		if(text != this.text && text.length) {

			this.text=text;
			this.text_container.innerHTML=this.text_to_view(this.text);
			this.save();
		}
	}

	set_as_editing() {

		this.dom_root.classList.add("edit");
		this.dom_root.classList.remove("read");
		this.textarea.focus();
	}

	cycle_color() {

		this.btn_color.disabled="disabled";

		let index=this.color_id+1;
		if(index > 5) {

			index=1;
		}

		this.set_color(index)
		.then( (_res) => {this.set_dom_color(this.color_id);})
		.catch( (_err) => {

			console.error(_err);
			alert(_err);
		})
		.finally( () => {

			this.btn_color.disabled=false;
		});
	}

	set_dom_color(_color_id) {

		[1,2,3,4,5].forEach( (_id) => {this.dom_root.classList.remove("color_0"+_id);});
		this.dom_root.classList.add("color_0"+_color_id);
	}

	set_color(_index) {

		this.color_id=_index;

		return new Promise( (_resolve, _reject) => {

			if(this.id) {

				return this.update().then( (_res)  => {_resolve(true);});
			}

			_resolve(true);
		});
	}

	save() {

		this.btn_edit.disabled="disabled";
		Promise.resolve(true)
		.then( () => {

			return !this.id
				? this.post()
				: this.update()
		})
		.then( () => {

			return this.api.get("notes/"+this.id, [200])
			.then( (_res) => {this.load_from_node(_res.body);});
		})
		.catch( (_err) => {

			console.error(_err);
			alert(_err);
		})
		.finally( () => {

			this.btn_edit.disabled=false;
		});
	}

	post() {

		return this.api.post("notes", {contents:this.text, color_id:this.color_id}, [201])
		.then( (_res) => {

				let location=_res.headers.get("location");
				this.id=parseInt(location.split("/").pop(), 10);
				this.dom_root.classList.remove("new");
				return true;
			}
		)
	}

	update() {

		return this.api.patch("notes/"+this.id, {contents:this.text, color_id:this.color_id}, [200])
			.then( (_res) => {

				return true;
			}
		);
	}

	delete() {

		if(this.id || this.textarea.value.trim().length) {

			if(!confirm("remove this note?")) {

				return;
			}
		}

		this.btn_delete.disabled="disabled";
		(
			this.id
				? this.api.delete("notes/"+this.id, {}, [200])
				: Promise.resolve(true)
		)
		.then( (_res) => {

			this.unload_events();
			this.dom_root.remove();
		})
		.catch( (_err) => {

			this.btn_delete.disabled=false;
			console.error(_err);
			alert(_err);
		});
	}

	text_to_view(_text) {


		let t=document.createElement("b");
		t.innerHTML=_text;

		let mapped=t.textContent.split("\n")
			.map( (_item) => {

				let contents=_item.trim();
				if(!contents.length) {

					contents="&nbsp;";
				}

				[
					{start: "\\-\\*", end:"\\*\\-", tag:"b"},
					{start: "\\-\\/", end:"\\/\\-", tag:"i"},
					{start: "\\-_", end:"_\\-", tag:"u"}
				].forEach( (_item) => {

					let re=new RegExp("("+_item.start+")(.*)("+_item.end+")");
					contents=contents.replace(re, "<"+_item.tag+">$2</"+_item.tag+">");
				});

				return "<p>"+contents+"</p>";
			})
			.join("\n");

		return mapped;
	}
}

function start_ui(
	_storage
) {
	//build workspace DOM...
	let workspace_prototype=document.getElementById("workspace");
	let wsclone=document.importNode(workspace_prototype.content, true);
	document.body.appendChild(wsclone);
	let ws=document.querySelector("#user_workspace");

	//build toolbar DOM...
	let toolbar_prototype=document.getElementById("toolbar");
	let tbclone=document.importNode(toolbar_prototype.content, true);
	document.body.appendChild(tbclone);
	let tb=document.querySelector("#user_toolbar");

	//build instances...
	let api_i=new api(_storage.getItem("auth-token"));

	let builder=new note_builder(
		document.getElementById("note"),
		ws,
		api_i
	);

	let tbar=new toolbar(
		tb,
		builder,
		api_i,
		_storage
	);

	api_i.get("notes", [200])
	.then( (_res) => {

		_res.body.forEach( (_node) => {

			let note=builder.build(false);
			note.setup_as_loaded();
			note.load_from_node(_node);
		});
		tbar.show();
	});

}
