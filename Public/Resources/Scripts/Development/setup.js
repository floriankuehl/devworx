import ElementUtility from '../Devworx/ElementUtility.js'
import Api from '../Devworx/Api.js'

import * as Elements from '../Devworx/Elements.js'
import * as ViewHelpers from '../Devworx/ViewHelpers.js'
import * as Editor from './Editor.js'

//ElementUtility.debug = true
ElementUtility.registerModules(Elements,ViewHelpers,Editor)

Api.context = 'development'
//Api.debug = true

const canvas = document.querySelector('devworx-view')

const board = Editor.Board.createElement((item)=>{
	item.classList.add('w-100')
	canvas.append(item)
})

const tables = [...canvas.querySelectorAll('devworx-table')]
tables.map(table=>table.addEventListener('click',e=>{
	e.preventDefault()
	e.stopPropagation()
	
	board.innerHTML = ''
	const tableName = table.getAttribute('name')
	Api.Get({
		controller:'Model',
		action:'schema',
		table:tableName
	}).then(json=>{
		board.loadNode(tableName,json[tableName])
	})
	
	//board.loadNode(
}))

/*
document.querySelector('#saveBoard').addEventListener('click',e=>{	
	Api.Post({controller:'Model',action:'check'},board.value)
		.then(json=>{
			console.log( json )
		})
})*/