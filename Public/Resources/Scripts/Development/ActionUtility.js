import ElementUtility from '../Devworx/ElementUtility.js'
import Action from './Action.js'

export default class ActionUtility {
	
	static cols = [8,2,2]
	
	static css(index){
		return `col-${this.cols[index]}`
	}
	
	static item(label,index){
		return ElementUtility.create(
			'div',
			label,
			['text-bg-dark','text-center',this.css(index)]
		)
	}
	
	static header(){
		return [
			this.item('Action',0),
			this.item('JSON',1),
			this.item('...',2)
		]
	}
	
	static instance(data){
		return Action.createElement((item)=>{
			item.classList.add('d-flex','flex-row')
			if( typeof data === 'object' ){
				if( Array.isArray(data) ){
					item.setAttribute('name',data[0])
					item.setAttribute('json',data[1])
					return item
				}
				item.setAttribute('name',data.name)
				item.setAttribute('json',data.json ? 1 : 0)
				return item
			}
			item.setAttribute('name',data)
			return item
		})
	}
	
	static map(rows){
		return rows.map(row=>Array.isArray(row) ? this.instance(...row) : this.instance(row))
	}
	
	static Ask(name=null,json=null){
		if( name === null ) name = prompt('Action name')
		if( json === null ) json = confirm('JSON?')
		
		return Action.createElement((item)=>{
			item.setAttribute('name',name)
			item.setAttribute('json',json ? 1 : 0)
		})
	}
}