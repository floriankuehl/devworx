export {default as Board} from './Board.js'
export {default as Node} from './Node.js'
export * from './Property.js'
export * from './Relation.js'
export * from './Action.js'
import Node from './Node.js'


export const MySQLTypes = [
  ['integer','INT',11],
  ['float','FLOAT',11],
  ['boolean','TINYINT',1],
  ['timestamp','TIMESTAMP',0],
  ['date','DATE',0],
  ['datetime','DATETIME',0],
  ['time','TIME',0],
  ['str4','VARCHAR',4],
  ['str32','VARCHAR',32],
  ['str64','TEXT',64],
  ['text','TEXT',0]
]

export function LoadNode(board,tableName,tableInfo,check=undefined){
	
	const node = Node.createElement(
		newNode => newNode.load(
			board,
			tableName,
			tableInfo,
			null,
			check.actions
		)
	)
	
	if( check ){
		if( check.controller.fileExists ){
			if( check.controller.classExists )
				node.addStatus('Controller','text-bg-success')
			else
				node.addStatus('Controller','text-bg-warning')
		} else
			node.addStatus('Controller','text-bg-danger')
		
		if( check.model.fileExists ){
			if( check.model.classExists )
				node.addStatus('Model','text-bg-success')
			else
				node.addStatus('Model','text-bg-warning')
		} else
			node.addStatus('Model','text-bg-danger')
		
		if( check.repository.fileExists ){
			if( check.repository.classExists )
				node.addStatus('Repository','text-bg-success')
			else
				node.addStatus('Repository','text-bg-warning')
		} else
			node.addStatus('Repository','text-bg-danger')
		
		if( check.template.fileExists )
			node.addStatus('Template','text-bg-success')
		else
			node.addStatus('Template','text-bg-danger')
	}
	return node
}