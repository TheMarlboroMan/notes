//define the login UI class
class login {

	constructor(_template, _storage, _info_bar) {

		this.storage=_storage;

		let clone=document.importNode(_template.content, true);
		document.body.appendChild(clone);

		this.container=document.querySelector("#login_container");
		this.form=this.container.querySelector("#form_login");
		this.message_container=this.container.querySelector("#login_message");
		this.info_bar=_info_bar;
		this.token="";
		this.hide();

		Promise.resolve(this.storage.getItem("auth-token"))
		.then( (_token) => {

			if(null===_token) {

				return false;
			}

			return this.attempt_access(_token)
			.then( (_res) => {

				if(! _res) {

					this.storage.removeItem("auth-token");
				}

				return _res;
			});
		})
		.then( (_res) => {

			if(!_res) {

				setup_form_submit(this.form, null, () => {this.login();});
				this.show();
				return;
			}

			this.start_application();
		})
		.catch( (_err) => {

			console.error(_err);
			this.show_error(_err);
		});
	}

	show() {

		this.container.style.display="flex";
	}

	hide() {

		this.container.style.display="none";
	}

	login() {

		this.form.btn_login.disabled="disabled";

		let payload={
			username: this.form.username.value.trim(),
			pass: this.form.pass.value.trim()
		};

		chain_fetch(
			document.baseURI+"api/login",
			{method:"POST", response:"full", type:"json", quickheaders:"json", body:JSON.stringify(payload)}
		)
		.then( (_res) => {

			if(400===_res.status_code) {

				this.show_error(_res.body);
				return;
			}

			if(200===_res.status_code) {

				let token=_res.headers.get("notes-auth_token");
				this.storage.setItem("auth-token", token);
				return this.attempt_access(token);
			}

			throw new Error("invalid status code");
		})
		.then( (_res) => {

			if(_res) {

				this.start_application();
			}
		})
		.catch( (_err) => {

			console.error(_err);
			this.show_error(_err);
		})
		.finally( () => {

			this.form.btn_login.disabled=false;
		});
	}

	attempt_access(_token) {

		return chain_fetch(
			document.baseURI+"api/session",
			{method:"POST", response:"full", type:"json", quickheaders:"json", body:JSON.stringify({token:_token})}
		)
		.then( (_res) => {

			if(404===_res.status_code) {

				this.show_error(_res.body);
				return false;
			}

			if(200===_res.status_code) {

				return true;
			}

			throw new Error("invalid status code");
		});
	}

	show_error(_text) {

		this.message_container.innerHTML=_text;
		this.message_container.classList.remove("hidden");
	}

	start_application() {

		this.hide();
		let tok=this.storage.getItem("auth-token").substr(0, 10);

		return new Promise( (_resolve, _reject) => {

			this.info_bar.info("retrieving application code...", 0);

			let uri=document.baseURI+"sassets/js/app.js?tok="+tok;
			let script=document.createElement("script");
			script.type="text/javascript";
			script.src=uri;
			script.onload=() => {

				_resolve();
			}

			document.head.appendChild(script);
		})
		.then( (_res) => {

			return new Promise( (_resolve, _reject) => {

				this.info_bar.info("retrieving application style...", 0);

				let uri=document.baseURI+"sassets/js/app.css?tok="+tok;
				let link=document.createElement("link");
				link.type="text/css";
				link.rel="stylesheet";
				link.href=uri;
				link.onload=() => {

					_resolve();
				}
				document.head.appendChild(link);
			});
		})
		.then( (_res) => {

			this.info_bar.info("retrieving application markup...", 0);

			return chain_fetch(
				document.baseURI+"sassets/js/app.html?tok="+tok,
				{method:"GET", response:"full", type:"text"}
			)
			.then( (_res) => {

				let b=document.createElement("b");
				b.innerHTML=_res.body;

				while(b.childNodes.length) {

					let node=b.firstChild;
					node.remove();
					document.body.appendChild(node);
				}
			});
		})
		.then( (_res) => {

			this.info_bar.info("starting application...", 0);

			start_ui(this.storage, this.info_bar);
		});
	}
}
