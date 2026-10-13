let _userInfo = localStorage.getItem('user');
let _role = localStorage.getItem('role');

let parsedUser = null;
try {
    if (_userInfo && _userInfo !== 'undefined' && _userInfo !== 'null') {
        parsedUser = JSON.parse(_userInfo);
    }
} catch (e) {
    parsedUser = null;
}

if (!parsedUser && typeof window !== 'undefined' && window.laravel && window.laravel.user) {
    parsedUser = window.laravel.user;
    _role = window.laravel.role || _role || 'Administrator';
    try {
        localStorage.setItem('user', JSON.stringify(parsedUser));
        localStorage.setItem('role', _role);
    } catch (e) {}
}

export default {
    namespaced: true,

    state: () => ({
        user: parsedUser || null,
        role: _role || null,
    }),

    mutations: {
        setAuthInfo: function (state, data) {
            state.user = data.user;
            state.role = data.role;
            let user = JSON.stringify(data.user);
            localStorage.setItem('user', user);
            localStorage.setItem('role', data.role);
        },
        removeAuthInfo: function (state) {
            state.role = null;
            state.user = null;
        }
    },

    actions: {
        logout(context) {
            localStorage.removeItem('user');
            context.commit('removeAuthInfo');
        },
        loginStore(context, data) {
            context.commit('setAuthInfo', data);
        }
    }
}
