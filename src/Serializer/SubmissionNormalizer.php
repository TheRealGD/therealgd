<?php

namespace App\Serializer;

use App\Entity\Submission;
use App\Entity\UserFlags;
use App\Utils\Slugger;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SubmissionNormalizer extends AbstractNormalizer {
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        CacheManager $liipCacheManager,
        RequestStack $requestStack,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter);

        $this->urlGenerator = $urlGenerator;
        $this->cacheManager = $liipCacheManager;
        $this->requestStack = $requestStack;
    }

    public function denormalize($data, $class, $format = null, array $context = []) {
        // TODO
    }

    public function supportsDenormalization($data, $type, $format = null): bool {
        return false;
    }

    public function normalize($object, $format = null, array $context = []): array {
        if (!$object instanceof Submission) {
            throw new \InvalidArgumentException();
        }

        $normalized = [
            'resource' => $this->urlGenerator->generate('submission', [
                'forum_name' => $object->getForum()->getName(),
                'submission_id' => $object->getId(),
                'slug' => Slugger::slugify($object->getTitle()),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'id' => $object->getId(),
            'forum' => $this->urlGenerator->generate('forum', [
                'forum_name' => $object->getForum()->getName(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'user' => $this->urlGenerator->generate('user', [
                'username' => $object->getUser()->getUsername(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'title' => $object->getTitle(),
            'body' => $object->getBody(),
            'url' => $object->getUrl(),
            'timestamp' => $object->getTimestamp()->format('c'),
            'locked' => $object->isLocked(),
            'sticky' => $object->isSticky(),
            'user_flag' => UserFlags::toReadable($object->getUserFlag()),
            'edited_at' => $object->getEditedAt()
                ? $object->getEditedAt()->format('c')
                : null,
            'moderated' => $object->isModerated(),
            'comment_count' => \count($object->getComments()),
            'upvotes' => $object->getUpvotes(),
            'downvotes' => $object->getDownvotes(),
        ];

        if ($object->getImage()) {
            $normalized['thumbnail_1x'] = $this->cacheManager->generateUrl(
                $object->getImage(),
                'submission_thumbnail_1x'
            );

            $normalized['thumbnail_2x'] = $this->cacheManager->generateUrl(
                $object->getImage(),
                'submission_thumbnail_2x'
            );
        }

        return \array_filter($normalized, function ($element) {
            return $element !== null;
        });
    }

    public function supportsNormalization($data, $format = null): bool {
        return $data instanceof Submission;
    }
}
