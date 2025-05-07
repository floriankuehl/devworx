export * from './Format.js'
export * from './Api.js'

import * as Elements from './Elements.js'
import * as ViewHelpers from './ViewHelpers.js'
import * as ProjectElements from './ProjectElements.js'

function autoRegisterClasses(module) {
  Object.keys(module).forEach((exportedKey) => {
    const exportedValue = module[exportedKey];
	if (typeof exportedValue === 'function' && exportedValue.prototype instanceof HTMLElement) {
      exportedValue.register();
      console.log(`Registered: ${exportedValue.name}`);
    }
  });
}

autoRegisterClasses(Elements)
autoRegisterClasses(ViewHelpers)
autoRegisterClasses(ProjectElements)
