//define the login UI class
class login {

	constructor() {

		this.storage=window.localStorage;
		this.form=document.getElementById("form_login");
		this.message_container=this.form.querySelector("#login_message");
		this.api=new api();

		if(null!==this.storage.getItem("auth-token")) {

			this.attempt_access(this.storage.getItem("auth-token"))
			return;
		}

		setup_form_submit(this.form, this.form.btn_login, () => {this.login();});
	}

	login() {

		this.form.btn_login.disabled="disabled";

		let payload={
			username: this.form.username.value.trim(),
			pass: this.form.pass.value.trim()
		};

		this.api.post("login", {payload}, [200, 400])
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

		this.api.post("session", {token:_token}, [200, 404])
		.then( (_res) => {

			if(404===_res.status_code) {

				throw new Error(_res.body);
			}

			//TODO: attempt to load the new JS document and stuff.
			console.log("all good now!");
			return true;
		})
		.catch( (_err) => {

			this.storage.removeItem("auth-token");
			console.error(_err);
			this.show_error(_err);
		});
	}

	show_error(_text) {

		this.message_container.innerHTML=_text;
		this.message_container.classList.remove("hidden");
	}

}
