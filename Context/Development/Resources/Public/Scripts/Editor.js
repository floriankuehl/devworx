export {default as Board} from './Board.js'
export {default as Node} from './Node.js'
export {default as Table} from './Table.js'

export {default as Property} from './Property.js'
export {default as Properties} from './Properties.js'
export {default as PropertyUtility} from './PropertyUtility.js'

export {default as Relation} from './Relation.js'
export {default as Relations} from './Relations.js'
export {default as RelationUtility} from './RelationUtility.js'

export {default as Action} from './Action.js'
export {default as Actions} from './Actions.js'
export {default as ActionUtility} from './ActionUtility.js'

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