import CustomElement from '../CustomElement.js'

export default class List extends CustomElement(HTMLElement) {
	
	constructor() { 
		super()
	}
  
	init() {
		const 
		  type = this.getAttribute('type'),
		  itemSelector = `devworx-${type.toLowerCase()}`,
		  items = this.querySelectorAll(itemSelector),
		  countInfo = this.querySelector('info count')

		this.setAttribute('count',items.length)
		if( countInfo ) countInfo.innerHTML = `${items.length} Einträge`
	}
}