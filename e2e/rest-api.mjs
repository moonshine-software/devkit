import http from 'node:http'

const port = Number(process.env.E2E_REST_PORT ?? 8100)
const roles = [{id: 1, name: 'Admin'}, {id: 2, name: 'Manager'}, {id: 3, name: 'User'}]
let users = Array.from({length: 30}, (_, index) => ({
    id: index + 1,
    name: `E2E API user ${index + 1}`,
    email: `api-user-${index + 1}@example.test`,
    phone: `+1555000${String(index + 1).padStart(4, '0')}`,
    role_id: index % 3 + 1,
    role: roles[index % 3],
    avatar: null,
    avatar_url: '',
}))

http.createServer(async (request, response) => {
    const url = new URL(request.url, `http://127.0.0.1:${port}`)
    const send = (body, status = 200) => {
        response.writeHead(status, {'Content-Type': 'application/json'})
        response.end(JSON.stringify(body))
    }
    if (url.pathname === '/health') return send({ok: true})
    if (request.headers.authorization !== 'Bearer test-api-token') return send({error: 'Unauthorized'}, 401)
    if (url.pathname === '/api/roles') return send({data: roles})
    if (url.pathname === '/api/users' && request.method === 'GET') {
        let data = users.filter(user => Object.entries(user).every(([key, value]) => {
            const filter = url.searchParams.get(`filter[${key}]`)
            return !filter || String(value).includes(filter)
        }))
        const page = Number(url.searchParams.get('page') ?? 1)
        const total = data.length
        data = data.slice((page - 1) * 25, page * 25)
        return send({data, links: [], path: '/api/users', from: (page - 1) * 25 + 1,
            to: Math.min(page * 25, total), total, last_page: Math.ceil(total / 25),
            first_page_url: '/api/users?page=1', last_page_url: `/api/users?page=${Math.ceil(total / 25)}`})
    }
    const match = url.pathname.match(/^\/api\/users\/(\d+)$/)
    if (match) {
        const id = Number(match[1])
        const user = users.find(user => user.id === id)
        if (!user) return send({error: 'Not found'}, 404)
        if (request.method === 'GET') return send(user)
        if (request.method === 'DELETE') {
            users = users.filter(user => user.id !== id)
            return send({ok: true})
        }
        if (request.method === 'PUT') {
            let body = ''
            for await (const chunk of request) body += chunk
            const data = JSON.parse(body)
            Object.assign(user, data, {id, role: roles.find(role => role.id === Number(data.role_id)) ?? user.role})
            return send(user)
        }
    }
    send({error: 'Unknown fixture endpoint'}, 404)
}).listen(port, '127.0.0.1')
