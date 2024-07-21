export class Api {
  
  static #cookie = 'devworx'
  static #debug = !true;
  
  static debug(value){
    this.#debug = !!value;
  }
  
  static cookie(){
    const name = this.#cookie + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        if( this.#debug ) console.log('Found cookie '+this.#cookie);
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
  
  static async Get(filter){
    const query = new URLSearchParams(filter).toString()
    const response = await fetch(
      `${window.location.origin}${window.location.pathname}?${query}`,
      {
        method: 'GET',
        mode: "same-origin", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": "application/json",
          "X-Devworx-Context": "api",
          "X-Devworx-Api": this.cookie()
        },
        redirect: "follow",
        referrerPolicy: "same-origin"
      }
    )
    if( this.#debug ) console.log('Api.Get',filter);
    return this.#debug ? response.text() : response.json()
  }
  
  static async Post(filter,data){
    const query = new URLSearchParams(filter).toString()
    const response = await fetch(
      `${window.location.origin}${window.location.pathname}?${query}`,
      {
        method: 'POST',
        mode: "same-origin", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
          "Content-Type": "application/json",
          "X-Devworx-Context": "api",
          "X-Devworx-Api": this.cookie()
        },
        redirect: "follow",
        referrerPolicy: "same-origin",
        body: JSON.stringify(data)
      }
    )
    if( this.#debug ) console.log('Api.Post',filter);
    return this.#debug ? response.text() : response.json()
  }
  
}