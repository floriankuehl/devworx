import CustomElement from '../CustomElement.js'

export default class Link extends CustomElement(HTMLElement){
	
	constructor(){
		super()
	}
	
	init(){
		const params = []
		if( this.hasAttribute('controller') ){
			params.push(`controller=${this.getAttribute('controller')}`)
			this.removeAttribute('controller')
		}	
		if( this.hasAttribute('action') ){
			params.push(`action=${this.getAttribute('action')}`)
			this.removeAttribute('action')
		}
		if( this.hasAttribute('uid') ){
			params.push(`uid=${this.getAttribute('uid')}`)
			this.removeAttribute('uid')
		}
		this.setAttribute('href',`?${params.join('&')}`)
		
		this.addEventListener('click',e=>{
			e.preventDefault()
			e.stopPropagation()
			window.location.href = this.getAttribute('href')
		})
	}
}