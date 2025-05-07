import {AutoRegistering} from './Elements.js'

export class Story extends AutoRegistering(HTMLAnchorElement) {
	
	static get baseTag(){ return 'a' }
	
	constructor() { 
		super()
	}
	
	connectedCallback() {
		const uid = this.getAttribute('uid')
		const action = this.getAttribute('action')
		this.removeAttribute('uid')
		this.removeAttribute('action')
		this.setAttribute('src',`?controller=Story&action=${action}&uid=${uid}`)
		
		this.addEventListener('click',e=>{
			e.preventDefault()
			e.stopPropagation()
			window.location.href = this.getAttribute('src')
		})
	}
}