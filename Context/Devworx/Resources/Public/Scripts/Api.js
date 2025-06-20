export default class Api {

	static Keys = {
	  contentType: 'Content-Type',
	  context: 'X-Devworx-Context',
	  api: 'X-Devworx-Api'
	}

	static Options = {
		debug: false,
		context: 'api',
		contentType: 'application/json',
		cookie: 'devworx',
		text:false
	}
	
	static Requests = {
		mode: "same-origin", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		redirect: "follow",
		referrerPolicy: "same-origin"
	}

	static #actions = {};

	static get context(){ return this.Options.context }
	static set context(value){ this.Options.context = value	}

	static get contentType(){ return this.Options.contentType }
	static set contentType(value){ this.Options.contentType = value	}

	static get text(){ return this.Options.text	}
	static set text(value){ this.Options.text = !!value	}
	
	static get debug(){ return this.Options.debug }
	static set debug(value){ this.Options.debug = !!value }

	static get cookie(){ return this.Options.cookie }
	static set cookie(value){ this.Options.cookie = value }

	static register(name,func){	this.#actions[name] = func	}
	static async trigger(name,...args){ return this.#actions[name](...args)	}

	static get apiKey(){
		const name = this.Options.cookie + "="
		let decodedCookie = decodeURIComponent(document.cookie)
		let ca = decodedCookie.split(';')
		for(let i = 0; i <ca.length; i++) {
			let c = ca[i].trim();
			if (c.indexOf(name) === 0) {
				if( this.Options.debug ) console.log('Found cookie '+this.Options.cookie)
				return c.substring(name.length, c.length)
			}
		}
		return ""
	}

	static get Headers(){
		return {
			[this.Keys.contentType]: this.Options.contentType,
			[this.Keys.context]: this.Options.context,
			[this.Keys.api]: this.apiKey
		}
	}
	
	static async Request(url,method,body=undefined){
		return fetch(url,{
			...this.Requests,
			headers: this.Headers,
			method: method,
			body: body
		})
	}
	
	static async Perform(method,filter=undefined,payload=undefined){
		if( this.debug ) console.log( 'Api.Perform', method, filter, payload )
		const 
			url = `${window.location.origin}${window.location.pathname}`,
			query = new URLSearchParams(filter??{}).toString(),
			body = ( typeof payload === 'object' ? JSON.stringify(payload) : payload ),
			response = await this.Request( `${url}?${query}`, method, body ),
			result = response[ this.text ? 'text' : 'json' ]()
		if( this.debug ) console.log( typeof response === 'object' ? response : response.text() )
		return result
	}

	static async Get(filter){ return this.Perform('GET',filter) }
	static async Post(filter,body){ return this.Perform('POST', filter, body) }
	static async Put(filter,body){ return this.Perform('PUT', filter, body) }
}