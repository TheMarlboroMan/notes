//define the login UI class
class login {

	constructor() {

		this.storage=window.localStorage;
		this.form=document.getElementById("form_login");
		this.message_container=this.form.querySelector("#login_message");
		this.api=new api();

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
			}
		})
		.catch( (_err) => {

			console.error(_err);
			this.show_error(_err);
		});
	}

	login() {

		this.form.btn_login.disabled="disabled";

		let payload={
			username: this.form.username.value.trim(),
			pass: this.form.pass.value.trim()
		};

		this.api.post("login", payload, [200, 400])
		.then( (_res) => {

			if(400===_res.status_code) {

				this.show_error(_res.body);
				return;
			}

			let token=_res.headers.get("x-notes-auth-token");
			this.storage.setItem("auth-token", token);
			return this.attempt_access(token);
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

		return this.api.post("session", {token:_token}, [200, 404])
		.then( (_res) => {

			if(404===_res.status_code) {

				throw new Error(_res.body);
			}

			//TODO: attempt to load the new JS document and stuff.
			console.log("all good now!");
			return true;
		});
	}

	show_error(_text) {

		this.message_container.innerHTML=_text;
		this.message_container.classList.remove("hidden");
	}

}
