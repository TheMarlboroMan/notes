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

class toolbar {

	constructor(_dom, _note_builder, _api, _storage) {

		this.dom_root=_dom;
		this.username_container=_dom.querySelector("#username_container");
		this.btn_new=_dom.querySelector("button[name='btn_new']");
		this.btn_settings=_dom.querySelector("button[name='btn_settings']");
		this.btn_logout=_dom.querySelector("button[name='btn_logout']");
		this.note_builder=_note_builder;
		this.api=_api;
		this.sanitizer_i=new sanitizer();
		this.storage=_storage;

		this.api.get("me", [200])
		.then( (_res) => {

			this.set_username(_res.body.username);
		})
		.then( (_res) => {

			this.btn_new.addEventListener("click", () => {this.new_note();}, true);
			this.btn_logout.addEventListener("click", () => {this.logout();}, true);

			//TODO: settings
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

		let note=this.note_builder.build();
		note.setup_as_new();
	}

	logout() {

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

	build() {

		let clone=document.importNode(this.note_template.content, true);
		this.workspace.insertBefore(clone, this.workspace.querySelector(".note:first-child"));
		let dom_note=this.workspace.querySelector(".note:first-child");

		return new note(dom_note, this.api);
	}
}

class note {

	constructor(_dom, _api) {

		this.editing=false;

		this.created_at=null;
		this.last_updated_at=null;
		this.id=0;
		this.text="";

		this.dom_root=_dom;
		this.api=_api;

		this.textarea=this.dom_root.querySelector("textarea");
		this.text_container=this.dom_root.querySelector(".body .text");
		this.btn_delete=this.dom_root.querySelector("button[name='btn_delete']");
		this.btn_color=this.dom_root.querySelector("button[name='btn_color']");
		this.btn_edit=this.dom_root.querySelector("button[name='btn_edit']");

		this.color_index=1;
		this.ev_btn_delete=this.btn_delete.addEventListener("click", () => {this.delete();}, true);
		this.ev_btn_color=this.btn_color.addEventListener("click", () => {this.cycle_color();}, true);
		this.ev_btn_edit=this.btn_edit.addEventListener("click", () => {this.toggle_edit();}, true);

		//the textarea has the "canonical" text.
		this.text=this.textarea.value.trim();
	}

	load_from_node(_node) {

		this.id=_node.id;
		this.created_at=_node.created_at;
		this.last_updated_at=_node.last_updated_at;
		this.text=_node.contents;

		this.text_container.innerHTML=this.text_to_view(this.text);
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

		//save if there were changes.
		if(text != this.text && text.length) {

			this.text=text;
			this.text_container.innerHTML=this.text_to_view(this.text);

			this.dom_root.classList.remove("edit");
			this.dom_root.classList.add("read");
			this.save();
		}
	}

	set_as_editing() {

		this.dom_root.classList.add("edit");
		this.dom_root.classList.remove("read");
		this.textarea.focus();
	}

	set_color(_index) {

		this.dom_root.classList.remove("color_0"+this.color_index);
		this.color_index=_index;
		this.dom_root.classList.add("color_0"+this.color_index);

		//TODO: Maybe save the note??? no, it might be empty!
	}

	cycle_color() {

		let index=this.color_index+1;
		if(index > 5) {

			index=1;
		}

		this.set_color(index);
	}

	save() {

		this.btn_edit.disabled="disabled";
		Promise.resolve(true)
		.then( () => {

			return !this.id
				? this.post()
				: this.update()
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

		return this.api.post("notes", {contents:this.text}, [201])
			.then( (_res) => {

				let location=_res.headers.get("location");
				this.id=parseInt(location.split("/").pop(), 10);
				return true;
			}
		);

		//TODO: Retrieve also the data for this note, so we can load its values again!!
	}

	update() {

		return this.api.patch("notes/"+this.id, {contents:this.text}, [200])
			.then( (_res) => {

				console.log(_res);
				return true;
			}
		);

		//TODO: retrieve the saved data, so we can put the values in place...
	}

	delete() {

		if(this.id || this.textarea.value.trim().length) {

			if(!confirm("remove this note?")) {

				return;
			}
		}

		this.btn_delete.disabled="disabled";
		this.api.delete("notes/"+this.id, {}, [200])
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

	//TODO: sanitize!

		let sanitizer_i=new sanitizer();

		let mapped=_text.split("\n")
			.map( (_item) => {


				return "<p>"+sanitizer_i.html(_item)+"</p>";
			})
			.join("\n");

		return mapped;
	}

	view_to_text(_text) {

		//TODO
	}

	unload_events() {

		this.btn_delete.removeEventListener("click", this.ev_btn_delete);
		this.btn_color.removeEventListener("click", this.ev_btn_color);
		this.btn_edit.removeEventListener("click", this.ev_btn_edit);
		this.ev_btn_delete=null;
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

			let note=builder.build();
			note.setup_as_loaded();
			note.load_from_node(_node);
		});
		tbar.show();
	});

}
