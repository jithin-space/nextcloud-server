import { loadState } from '@nextcloud/initial-state'

const forms = loadState('settings', 'declarative-settings-forms', []) as Array<any>;

console.log('Declarative settings forms!');
console.log(forms);
