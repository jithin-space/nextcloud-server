/**
 * @copyright Copyright (c) 2024 Fon E. Noel NFEBE <fenn25.fn@gmail.com>
 *
 * @author Fon E. Noel NFEBE <fenn25.fn@gmail.com>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

/**
 * Fetches the conversations from the server.
 *
 * @param {object} options options
 */
const fetchConversations = async function(options) {
	options = options || {}
	options.params = options.params || {}
	options.params.includeStatus = true
	return await axios.get(generateOcsUrl('apps/spreed/api/v4/room'), options)
}

/**
 * Fetches a conversation from the server.
 *
 * @param {string} token The token of the conversation to be fetched.
 */
const fetchConversation = async function(token) {
	return axios.get(generateOcsUrl('apps/spreed/api/v4/room/{token}', { token }))
}

/**
 * Fetch listed conversations
 *
 * @param {string} searchText The string that will be used in the search query.
 * @param {object} options options
 */
const searchListedConversations = async function({ searchText }, options) {
	return axios.get(generateOcsUrl('apps/spreed/api/v4/listed-room'), Object.assign(options, {
		params: {
			searchTerm: searchText,
		},
	}))
}


export {
	fetchConversations,
	fetchConversation,
	searchListedConversations,
}
