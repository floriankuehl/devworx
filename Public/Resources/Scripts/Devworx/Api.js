export class Api {
  
  static #cookie = 'devworx'
  static #debug = !true
  static #text = !true
  
  static #actions = {};
  
  static text(value){
    this.#text = !!value
    return this;
  }
  
  static debug(value){
    this.#debug = !!value
    return this;
  }
  
  static register(name,func){
    this.#actions[name] = func
  }
   
  static async trigger(name,...args){
    return this.#actions[name](...args)
  }
  
  static cookie(){
    const name = this.#cookie + "="
    let decodedCookie = decodeURIComponent(document.cookie)
    let ca = decodedCookie.split(';')
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1)
      }
      if (c.indexOf(name) == 0) {
        if( this.#debug ) console.log('Found cookie '+this.#cookie)
        return c.substring(name.length, c.length)
      }
    }
    return ""
  }
  
  static async Get(filter,context='api',contentType='application/json'){
    const query = new URLSearchParams(filter).toString()
    const response = await fetch(
      `${window.location.origin}${window.location.pathname}?${query}`,
      {
        method: 'GET',
        mode: "same-origin", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": contentType,
          "X-Devworx-Context": context,
          "X-Devworx-Api": this.cookie()
        },
        redirect: "follow",
        referrerPolicy: "same-origin"
      }
    )
    if( this.#debug ) console.log('Api.Get',filter);
    return this.#debug || this.#text ? response.text() : response.json()
  }
  
  static async Post(filter,data,context='api',contentType='application/json'){
    const query = new URLSearchParams(filter).toString()
    const response = await fetch(
      `${window.location.origin}${window.location.pathname}?${query}`,
      {
        method: 'POST',
        mode: "same-origin", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": contentType,
          "X-Devworx-Context": context,
          "X-Devworx-Api": this.cookie()
        },
        redirect: "follow",
        referrerPolicy: "same-origin",
        body: JSON.stringify(data)
      }
    )
    if( this.#debug ) console.log('Api.Post',filter,data)
    return this.#debug || this.#text ? response.text() : response.json()
  }
  
}
