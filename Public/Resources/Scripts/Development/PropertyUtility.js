import CustomElement from '../Devworx/CustomElement.js'
import ElementUtility from '../Devworx/ElementUtility.js'
import Property from './Property.js'

export default class PropertyUtility {
	
	static cols = [2,1,1,1,1, 1,1,2,2]
	
	static css(index){
		return `col-${this.cols[index]}`
	}
	
	static item(label,index){
		return ElementUtility.create('div',label,['text-bg-dark','text-center',this.css(index)])
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
			item.classList.add('d-flex','flex-row')
			if( data ) item.setAttributes(data)
		})
	}
	
	static map(rows){
		return rows.map(row=>this.instance(row))
	}
	
	static updateMovables(container){
		if( container ){
			const list = [...container.querySelectorAll('devworx-property:not([disabled])')]
			return list.map((prop,index)=>{
				prop.canMoveUp = index > 0
				prop.canMoveDown = index < ( list.length - 1 )
				return prop
			})
			
		}
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