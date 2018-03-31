<?php

namespace App\Serializer;

use App\Entity\Comment;
use App\Entity\UserFlags;
use App\Utils\Slugger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class CommentNormalizer extends AbstractNormalizer {
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null
    ) {
        parent::__construct($classMetadataFactory, $nameConverter);

        $this->urlGenerator = $urlGenerator;
    }

    public function denormalize($data, $class, $format = null, array $context = []) {
        // TODO: Implement denormalize() method.
    }

    public function supportsDenormalization($data, $type, $format = null): bool {
        return false;
    }

    public function normalize($object, $format = null, array $context = []): array {
        if (!$object instanceof Comment) {
            throw new \InvalidArgumentException();
        }

        $normalized = [
            'resource' => $this->urlGenerator->generate('comment', [
                'forum_name' => $object->getSubmission()->getForum()->getName(),
                'submission_id' => $object->getSubmission()->getId(),
                'comment_id' => $object->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'id' => $object->getId(),
            'body' => $object->getBody(),
            'timestamp' => $object->getTimestamp()->format('c'),
            'user' => $this->urlGenerator->generate('user', [
                'username' => $object->getUser()->getUsername(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'submission' => $this->urlGenerator->generate('submission', [
                'forum_name' => $object->getSubmission()->getForum()->getName(),
                'submission_id' => $object->getSubmission()->getId(),
                'slug' => Slugger::slugify($object->getSubmission()->getTitle()),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'parent' => $object->getParent()
                ? $this->urlGenerator->generate('comment', [
                    'forum_name' => $object->getSubmission()->getForum()->getName(),
                    'submission_id' => $object->getSubmission()->getId(),
                    'comment_id' => $object->getParent()->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL)
                : null,
            'reply_count' => \count($object->getChildren()),
            'upvotes' => $object->getUpvotes(),
            'downvotes' => $object->getDownvotes(),
            'soft_deleted' => $object->isSoftDeleted(),
            'edited_at' => $object->getEditedAt()
                ? $object->getEditedAt()->format('c')
                : null,
            'moderated' => $object->isModerated(),
            'user_flag' => UserFlags::toReadable($object->getUserFlag()),
        ];

        return \array_filter($normalized, function ($element) {
            return $element !== null;
        });
    }

    public function supportsNormalization($data, $format = null): bool {
        return $data instanceof Comment;
    }
}
