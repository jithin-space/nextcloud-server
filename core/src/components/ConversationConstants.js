export const CONVERSATION = {
	START_CALL: {
		EVERYONE: 0,
		USERS: 1,
		MODERATORS: 2,
	},

	STATE: {
		READ_WRITE: 0,
		READ_ONLY: 1,
	},

	LISTABLE: {
		NONE: 0,
		USERS: 1,
		ALL: 2,
	},

	TYPE: {
		ONE_TO_ONE: 1,
		GROUP: 2,
		PUBLIC: 3,
		CHANGELOG: 4,
		ONE_TO_ONE_FORMER: 5,
		NOTE_TO_SELF: 6,
	},

	BREAKOUT_ROOM_MODE: {
		NOT_CONFIGURED: 0,
		AUTOMATIC: 1,
		MANUAL: 2,
		FREE: 3,
	},

	BREAKOUT_ROOM_STATUS: {
		// Main room
		STOPPED: 0,
		STARTED: 1,
		// Breakout rooms
		STATUS_ASSISTANCE_RESET: 0,
		STATUS_ASSISTANCE_REQUESTED: 2,
	},

	OBJECT_TYPE: {
		EMAIL: 'emails',
		FILE: 'file',
		PHONE: 'phone',
		VIDEO_VERIFICATION: 'share:password',
		BREAKOUT_ROOM: 'room',
		DEFAULT: '',
	},
}

export const AVATAR = {
	SIZE: {
		EXTRA_SMALL: 22,
		SMALL: 32,
		DEFAULT: 44,
		MEDIUM: 64,
		LARGE: 128,
		EXTRA_LARGE: 180,
		FULL: 512,
	},
}
