class api {

	constructor(_token) {

		this.root=document.baseURI+"api/";
		this.token=_token;
	}

	post(_endpoint, _payload, _expected) {

		let method="POST";

		return chain_fetch(
			this.root+_endpoint,
			{method:method, response:"full", type:"json", headers:this.build_headers(method), body:JSON.stringify(_payload)}
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

			this.show();
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

	constructor(_note_template, _workspace) {

		this.note_template=_note_template;
		this.workspace=_workspace;
	}

	build() {

		let clone=document.importNode(this.note_template.content, true);
		this.workspace.insertBefore(clone, this.workspace.querySelector(".note:first-child"));
		let dom_note=this.workspace.querySelector(".note:first-child");

		return new note(dom_note);
	}
}

class note {

	constructor(_dom) {

		this.dom_root=_dom;
		this.textarea=this.dom_root.querySelector("textarea");
		this.text_container=this.dom_root.querySelector(".body .text");
		this.btn_delete=this.dom_root.querySelector("button[name='btn_delete']");
		this.btn_color=this.dom_root.querySelector("button[name='btn_color']");

		this.color_index=1;
		this.ev_btn_delete=this.btn_delete.addEventListener("click", () => {this.delete();}, true);
		this.ev_btn_color=this.btn_color.addEventListener("click", () => {this.cycle_color();}, true);
	}

	setup_as_new() {

		this.dom_root.classList.add("new");
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

	delete() {

		console.log(this.textarea.value);
		console.log(this.text_container.innerText);

		if(this.textarea.value.trim().length || this.text_container.innerText.length) {

			if(!confirm("remove this note?")) {

				return;
			}
		}

		this.unload_events();
		this.dom_root.remove();
	}

	unload_events() {

		this.btn_delete.removeEventListener("click", this.ev_btn_delete);
		this.ev_btn_delete=null;
	}
}

function start_ui(
	_storage
) {
	let api_i=new api(_storage.getItem("auth-token"));

	//build workspace...
	let workspace_prototype=document.getElementById("workspace");
	let wsclone=document.importNode(workspace_prototype.content, true);
	document.body.appendChild(wsclone);
	let ws=document.querySelector("#user_workspace");

	//build toolbar!
	let toolbar_prototype=document.getElementById("toolbar");
	let tbclone=document.importNode(toolbar_prototype.content, true);
	document.body.appendChild(tbclone);
	let tb=document.querySelector("#user_toolbar");

	let builder=new note_builder(
		document.getElementById("note"),
		ws
	);

	let tbar=new toolbar(
		tb,
		builder,
		api_i,
		_storage
	);

	//TODO: do we need a note manager????

	tbar.set_username("newuser");
}
