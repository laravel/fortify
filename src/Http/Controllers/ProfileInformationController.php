<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;
use ReflectionException;
use ReflectionMethod;

class ProfileInformationController extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  Request  $request
     * @param  UpdatesUserProfileInformation  $updater
     * @return ProfileInformationUpdatedResponse
     */
    public function update(Request $request, UpdatesUserProfileInformation $updater)
    {
        if (config('fortify.lowercase_usernames') && $request->has(Fortify::username())) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        $updater->update($request->user(), $this->resolveInput($updater, $request));

        return app(ProfileInformationUpdatedResponse::class);
    }

    /**
     * Resolve the input for the updater based on the expected parameter type.
     *
     * @param  UpdatesUserProfileInformation  $updater
     * @param  Request  $request
     * @return Request|array
     */
    protected function resolveInput(UpdatesUserProfileInformation $updater, Request $request): Request|array
    {
        try {
            $reflection = new ReflectionMethod($updater::class, 'update');
            $parameter = $reflection->getParameters()[1] ?? null;

            $type = $parameter?->getType();

            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                $name = $type->getName();

                if ($name === Request::class || is_subclass_of($name, Request::class)) {
                    return $request;
                }
            }
        } catch (ReflectionException) {
            // Fallback for mocks or dynamic implementations.
        }

        return $request->all();
    }
}
