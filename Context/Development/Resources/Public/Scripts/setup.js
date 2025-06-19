import ElementUtility from '/resources/devworx/Scripts/ElementUtility.js'
import Api from '/resources/devworx/Scripts/Api.js'

import * as Elements from '/resources/devworx/Scripts/Elements.js'
import * as ViewHelpers from '/resources/devworx/Scripts/ViewHelpers.js'
import * as Editor from './Editor.js'

ElementUtility.debug = true
ElementUtility.registerModules(Elements,ViewHelpers,Editor)

Api.context = 'Development'
//Api.debug = true

const canvas = document.querySelector('devworx-view')

const board = Editor.Board.createElement((item)=>{
	item.classList.add('w-100')
	canvas.append(item)
})

const tables = [...canvas.querySelectorAll('devworx-table')]
tables.map(item=>item.addEventListener('click',e=>{
	e.preventDefault()
	e.stopPropagation()
	
	board.innerHTML = ''
	const table = item.getAttribute('name')
	const context = item.getAttribute('context')
	
	//Api.text = true
	Api.Get({
		controller:'Model',
		action:'schema',
		table:table,
		context: context
	}).then(json=>{
		//board.innerHTML = json
		board.loadNode(table,json[table])
	})	
}))


document.querySelector('#checkBoard').addEventListener('click',e=>{	
	Api.Post(
		{controller:'model',action:'check'},
		board.value
	).then(json=>{
		console.log( json )
	})
})