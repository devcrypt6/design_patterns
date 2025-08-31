<?php
declare(strict_types=1);

/**
 * Template Method Design Pattern
 * 
 * Define the skeleton of an algorithm in an operation, deferring some steps to subclasses.
 * 
 * Template Method lets subclasses redefine certain steps of an algorithm without changing the algorithm's structure.
 */
abstract class AuthController
{
    public final function handle(array $request): array
    {
        $input = $this->parse($request);
        $this->validate($input);
        $user  = $this->authenticate($input);
        return $this->response($user);
    }

    // Steps to customize
    abstract protected function parse(array $req): array;
    abstract protected function validate(array $input): void;
    abstract protected function authenticate(array $input): array;

    // Shared default
    protected function response(array $user): array
    {
        return ['status' => 200, 'user' => $user];
    }
}

final class EmailPasswordAuth extends AuthController
{
    protected function parse(array $req): array
    {
        return ['email' => trim($req['email'] ?? ''), 'password' => (string)($req['password'] ?? '')];
    }
    protected function validate(array $in): void
    {
        if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL) || $in['password'] === '') {
            throw new RuntimeException('Invalid credentials');
        }
    }
    protected function authenticate(array $in): array
    {
        // pretend DB lookup
        if ($in['email'] === 'a@b.c' && $in['password'] === 'secret') {
            return ['id' => 1, 'email' => $in['email']];
        }
        throw new RuntimeException('Auth failed');
    }
}

final class OAuthAuth extends AuthController
{
    protected function parse(array $req): array
    {
        return ['provider' => $req['provider'] ?? '', 'token' => $req['token'] ?? ''];
    }
    protected function validate(array $in): void
    {
        if (!in_array($in['provider'], ['google','github'], true) || $in['token'] === '') {
            throw new RuntimeException('Invalid OAuth input');
        }
    }
    protected function authenticate(array $in): array
    {
        // pretend token verification
        return ['id' => 42, 'provider' => $in['provider']];
    }
}

// Demo 
$ctrl = new EmailPasswordAuth();
print_r($ctrl->handle(['email'=>'a@b.c','password'=>'secret']));

$oauth = new OAuthAuth();
print_r($oauth->handle(['provider'=>'google','token'=>'xyz']));
