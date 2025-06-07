import CustomElement from '../Devworx/CustomElement.js'
import List from '../Devworx/Elements/List.js'
import ElementUtility from '../Devworx/ElementUtility.js'

export class PropertyUtility {
	
	static cols = [2,1,1,1,1, 1,1,2,2]
	
	static css(index){
		return `col-${this.cols[index]}`
	}
	
	static item(label,index){
		const result = document.createElement('div')
		result.innerHTML = label
		result.classList.add('text-bg-dark','text-center',this.css(index))
		return result
	}
	
	static header(){
		return [
			this.item('Name',0),
			this.item('Index',1),
			this.item('PHP',2),
			this.item('DB',3),
			this.item('Length',4),
			this.item('Standard',5),
			this.item('Null',6),
			this.item('Extra',7),
			this.item('...',8)
		]
	}
	
	static instance(data){
		return Property.createElement((item)=>{
			item.setAttribute('name',data.name)
			item.setAttribute('key',data.key)
			item.setAttribute('value',data.value)
			item.setAttribute('phptype',data.phpType)
			item.setAttribute('dbtype',data.typeName)
			item.setAttribute('length',data.length)
			item.setAttribute('nullable',data.nullable)
			item.setAttribute('extra',data.extra)
		})
	}
	
	static map(rows){
		return rows.map(row=>this.instance(row))
	}
	
	static get keyOptions(){
		return ElementUtility.options([
			['',''],
			['PRI','Primary'],
			['MUL','Multiple'],
			['UNI','Unique'],
			['FULLTEXT','Fulltext']
		])
	}
	
	static get phptypeOptions(){
		return ElementUtility.options({
			'characters': [
				'string', 
			],
			'numbers': [
				'int', 
				'float', 
				'byte'
			],
			'date': [
				'DateTime'
			],
			'logic': [
				'bool'
			],
			'others': [
				'mixed',
				'object', 
				'array'
			]
		})
	}
	
	static get dbtypeOptions(){
		return ElementUtility.options({
			'characters': [
				'varchar',
				'text',
				'json'
			],
			'numbers': [
				'int',
				'tinyint',
				'smallint',
				'mediumint',
				'bigint',
				'float',
				'decimal'
			],
			'date': [
				'date',
				'time',
				'datetime',
				'timestamp'
			]
		})
	}
	
	static model(fields){
		
		const prepend = fields.filter((row)=>{
			return row.key == 'PRI'
		})
		const others = fields.filter((row)=>{
			return !row.system
		})
		const append = fields.filter((row)=>{
			return row.system && row.key !== 'PRI'
		})
		
		return {
			prepend: this.map(prepend),
			append: this.map(append),
			fields: this.map(others)
		}
	}
	
	static Ask(name=null,key=null,phptype=null,dbtype=null,length=null,nullable=null,value=null,extra=null){
		if( name === null ) name = prompt('Property name')
		if( key === null ) key = prompt('Index')
		if( phptype === null ) phptype = prompt('PHP type','string')
		if( dbtype === null ) dbtype = prompt('SQL type','varchar')
		if( length === null ) length = prompt('Field length',32)
		if( nullable === null ) nullable = confirm('Nullable?')
		if( value === null ) value = prompt('Default value','')
		if( extra === null ) extra = prompt('Extra')

		return Property.createElement((item)=>{
			item.setAttribute('name',name)
			item.setAttribute('key',key)
			item.setAttribute('phptype',phptype)
			item.setAttribute('dbtype',dbtype)
			item.setAttribute('length',length)
			item.setAttribute('nullable',nullable?1:0)
			item.setAttribute('value',value)
			item.setAttribute('extra',extra)
		})
	}
}

export const UpdateMovableProperties = (container)=>{
	const list = [...container.querySelectorAll('devworx-property:not([disabled])')]
	list.forEach((prop,index)=>{
		prop.canMoveUp = index > 0
		prop.canMoveDown = index < ( list.length - 1 )
	})
}

export class Property extends CustomElement(HTMLElement){
	
	#name
	#key
	#phptype
	#dbtype
	#length
	#nullable
	#value
	#extra
	
	#moveUp
	#moveDown
	#remove
	
	//static get baseTag(){ return 'li' }
	
	constructor() { 
		super()
								
		this.#moveUp = ElementUtility.create('button','&uarr;',['btn'])
		this.#moveDown = ElementUtility.create('button','&darr;',['btn'])
		this.#remove = ElementUtility.create('button','x',['btn'])
		
		this.#name = ElementUtility.create('input',{type:'text'})
		this.#key = ElementUtility.create('select',PropertyUtility.keyOptions)
		this.#phptype = ElementUtility.create('select',PropertyUtility.phptypeOptions)
		this.#dbtype = ElementUtility.create('select',PropertyUtility.dbtypeOptions)
		this.#length = ElementUtility.create('input',{type:'number'})
		this.#value = ElementUtility.create('input',{type:'text'})
		this.#nullable = ElementUtility.create('input',{type:'checkbox',value:1})
		this.#extra = ElementUtility.create('input',{type:'text'})
    }
	
	set canMoveUp(state=true){
		this.#moveUp.classList[ state ? 'remove' : 'add' ]('invisible')
	}
	
	set canMoveDown(state=true){
		this.#moveDown.classList[ state ? 'remove' : 'add' ]('invisible')
	}
	
	init(){
		super.init()
		//console.log( 'initialize', this.localName, this.getAttribute('name') )
		
		this.classList.add('d-flex','flex-row');
		
		const variable = 'properties'
		this.#name.setAttribute('name',`${variable}[name][]`)
		this.#name.setAttribute('value', this.getAttribute('name') ?? '')
		
		this.#key.setAttribute('name',`${variable}[key][]`)
		this.#key.value = this.getAttribute('key') ?? ''
		
		this.#phptype.setAttribute('name',`${variable}[phptype][]`)
		this.#phptype.value = this.getAttribute('phptype') ?? ''
		
		this.#dbtype.setAttribute('name',`${variable}[dbtype][]`)
		this.#dbtype.value = this.getAttribute('dbtype') ?? ''
		
		this.#length.setAttribute('name',`${variable}[length][]`)
		this.#length.value = this.getAttribute('length') ?? ''
		
		this.#value.setAttribute('name',`${variable}[value][]`)
		this.#value.setAttribute('value', this.getAttribute('value') ?? '')
		
		this.#nullable.setAttribute('name',`${variable}[nullable][]`)
		this.#nullable.setAttribute('value', 1)
		this.#nullable.checked = this.hasAttribute('nullable') && this.getAttribute('nullable') == 1
		
		this.#extra.setAttribute('name',`${variable}[extra][]`)
		this.#extra.setAttribute('value', this.getAttribute('extra') ?? '')
		
		this.#moveUp.addEventListener('click',e=>{
			this.previousSibling.before( this )
			this.classList.add('active')
			UpdateMovableProperties(this.parentNode)
			setTimeout(_=>this.classList.remove('active'),500)
		})
		
		this.#moveDown.addEventListener('click',e=>{
			this.nextSibling.after(this)
			this.classList.add('active')
			UpdateMovableProperties(this.parentNode)
			setTimeout(_=>this.classList.remove('active'),500)
		})
		
		this.#remove.addEventListener('click',e=>{
			const p = this.parentNode
			this.remove()
			UpdateMovableProperties(p)
		})
		
		this.append(
			ElementUtility.create('div',this.#name,['p-1',PropertyUtility.css(0)]), 
			ElementUtility.create('div',this.#key,['p-1',PropertyUtility.css(1)]),
			ElementUtility.create('div',this.#phptype,['p-1', PropertyUtility.css(2)]),
			ElementUtility.create('div',this.#dbtype,['p-1', PropertyUtility.css(3)]),
			ElementUtility.create('div',this.#length,['p-1', PropertyUtility.css(4)]),
			ElementUtility.create('div',this.#value,['p-1', PropertyUtility.css(5)]), 
			ElementUtility.create('div',this.#nullable,['p-1', PropertyUtility.css(6)]),
			ElementUtility.create('div',this.#extra,['p-1', PropertyUtility.css(7)]),
			ElementUtility.create('div',[this.#moveUp,this.#moveDown,this.#remove],['p-1','d-flex','flex-row','gap-2',PropertyUtility.css(8)])
		)
		
		UpdateMovableProperties(this.parentNode)
	}
	
	get value(){
		return {
			name: this.#name.value,
			key: this.#key.value,
			phpType: this.#phptype.value,
			dbType: this.#dbtype.value,
			length: this.#length.value,
			value: this.#value.value,
			nullable: this.#nullable.checked,
			extra: this.#extra.value
		}
	}
	
	json(){
		return JSON.stringify(this.value)
	}
}

export class Properties extends List {
	
	#prepend
	#append
	#fields
	
	//static baseTag(){ return 'devworx-node' }
	
	constructor(){
		super()
		
		this.#prepend = document.createElement('fieldset')
		this.#append = document.createElement('fieldset')
		this.#fields = document.createElement('fieldset')
	}
	
	load(properties=undefined){
		if( properties ){
			const fields = PropertyUtility.model(properties)
			this.#prepend.innerHTML = ''
			this.#prepend.append(...fields.prepend)
			this.#append.innerHTML = ''
			this.#append.append(...fields.append)
			this.#fields.innerHTML = ''
			this.#fields.append(...fields.fields)
			
			this.disableProperties(this.#append)
			this.disableProperties(this.#prepend)
		}
	}
	
	disableProperties(item){
		item.setAttribute('disabled','true')
		item.classList.add('text-bg-light')
		item.querySelectorAll('input, select, textarea').forEach(i=>{
			i.setAttribute('readonly','true')
			i.setAttribute('disabled','true')
		})
		item.querySelectorAll('button').forEach(i=>i.remove())
		return item
	}
	
	init(){
		super.init()
		this.append(
			this.#prepend,
			this.#fields,
			this.#append
		)
		this.setAttribute('type','Property')
	}
	
	get value(){
		return [...this.querySelectorAll('devworx-property')].map(p=>p.value)
	}
}