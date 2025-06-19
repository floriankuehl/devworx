import CustomElement from '../CustomElement.js'

export default class ID extends CustomElement(HTMLSpanElement) {
	
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'span' }
  
  connectedCallback(){
    const 
      value = '' + parseInt( this.getAttribute('value') ),
      prefix = this.hasAttribute('prefix') ? this.getAttribute('prefix') : '',
      postfix = this.hasAttribute('postfix') ? this.getAttribute('postfix') : '',
      length = parseInt( this.getAttribute('length') )
    
    this.innerHTML = `${prefix}${value.padStart(length,'0')}${postfix}`
  }
}