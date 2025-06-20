import Format from '../Format.js'
import CustomElement from '../CustomElement.js'

export default class Time extends CustomElement(HTMLTimeElement) {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'time' }
   
  connectedCallback(){
    const dt = this.getAttribute('datetime')
    if( dt == '' || dt == 'null' || dt == '0000-00-00 00:00:00' ) 
      return;
    const d = new Date(dt)
    if( this.hasAttribute('dateonly') )
      this.innerHTML = Format.date(d)
    else
      this.innerHTML = Format.dateTime(d)
  }
}
