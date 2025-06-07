import ElementUtility from '../Devworx/ElementUtility.js'
import Api from '../Devworx/Api.js'

import * as Elements from '../Devworx/Elements.js'
import * as ViewHelpers from '../Devworx/ViewHelpers.js'
import * as Editor from './Editor.js'

ElementUtility.debug = true
ElementUtility.registerModules(Elements,ViewHelpers,Editor)

Api.context = 'development'
//Api.debug = true

const canvas = document.querySelector('devworx-view')

const board = Editor.Board.createElement((item)=>{
	item.classList.add('bg-white','w-100')
	canvas.append(item)
})

document.querySelector('#saveBoard').addEventListener('click',e=>{
	
	Api.Post({controller:'Model',action:'check'},board.value)
		.then(json=>{
			console.log( json )
		})
})

document.querySelector('#loadBoard').addEventListener('click',e=>{
	board.querySelectorAll('devworx-node').forEach(node=>node.remove())
	Api.Get({controller:'Model',action:'schema'})
		.then(json=>{
			for( let table of Object.keys(json.table) ){
				Editor.LoadNode(
					board,
					table,
					json.table[table],
					json.check[table]
				)
			}
			board.load()
		})
})